<?php

declare(strict_types=1);

namespace Modules\Common\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;

class HealthCheckCommand extends Command
{
    const STATE_OK = 'ok';
    const STATE_WARNING = 'warning';
    const STATE_FAILING = 'failing';

        /**
     * The console command name.
     */
    protected $name = 'health:check';

    /**
     * The console command description.
     */
    protected $description = 'Health check.';

    protected array $except = [
        '*Queue',
    ];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        collect((new \ReflectionObject($this))->getMethods(\ReflectionMethod::IS_PROTECTED | \ReflectionMethod::IS_PRIVATE))
            ->filter(fn (\ReflectionMethod $method) => Str::of($method->name)->startsWith('check'))
            ->reject(fn (\ReflectionMethod $method) => Str::of($method->name)->is($this->except))
            ->sortBy(fn (\ReflectionMethod $method) => $method->name)
            ->pipe(function (Collection $methods) {
                $this->withProgressBar($methods, function ($method) use (&$checks): void {
                    $result = \call_user_func([$this, $method->name]);

                    $checks[] = [
                        'index' => \count((array) $checks) + 1,
                        'resource' => Str::of($method->name)->replaceFirst('check', ''),
                        'state' => $result['state'],
                        'message' => $result['description'],
                    ];
                });

                $this->newLine();
                $this->table(['Index', 'Resource', 'State', 'Message'], $checks);

                return collect($checks);
            })
            ->filter(fn ($check) => $check['state'] !== self::STATE_OK)
            ->whenNotEmpty(function (Collection $notOkChecks) {
                $this->error('Health check failed.');

                return $notOkChecks;
            })
            ->whenEmpty(function (Collection $notOkChecks) {
                $this->info('Health check passed.');

                return $notOkChecks;
            });

        return self::SUCCESS;
    }

    protected function checkDatabase($connection = null): array
    {
        try {
            DB::connection($connection ?: config('database.default'))->getPdo();
        } catch (\Throwable $e) {
            return $this->failing("Could not connect to the database: `{$e->getMessage()}`");
        }

        return $this->ok('The database check passed');
    }

    protected function checkDatabaseVersion($connection = null): array
    {
        if ('mysql' !== config('database.default')) {
            return $this->warning('This check is only available for MySQL.');
        }

        $databaseVersion = DB::select('SELECT VERSION() as version')[0];
        if ($databaseVersion->version < '5.7.0') {
            return $this->failing('Mysql version is less than 5.7.0.');
        }

        return $this->ok('Mysql version check passed');
    }

    protected function checkSqlSafeUpdates(): array
    {
        if ('mysql' !== config('database.default')) {
            return $this->warning('This check is only available for MySQL.');
        }

        $sqlSafeUpdates = DB::select("SHOW VARIABLES LIKE 'sql_safe_updates' ")[0];
        if (! Str::of($sqlSafeUpdates->Value)->lower()->is('on')) {
            return $this->failing('`sql_safe_updates` is disabled. Please enable it.');
        }

        return $this->ok('The database sql_safe_updates config check passed');
    }

    protected function checkSqlMode($checkedSqlModes = 'strict_all_tables'): array
    {
        if ('mysql' !== config('database.default')) {
            return $this->warning('This check is only available for MySQL.');
        }

        $sqlModes = DB::select("SHOW VARIABLES LIKE 'sql_mode' ")[0];

        /** @var Collection $diffSqlModes */
        $diffSqlModes = Str::of($sqlModes->Value)
            ->lower()
            ->explode(',')
            ->pipe(function (Collection $sqlModes) use ($checkedSqlModes): Collection {
                return collect($checkedSqlModes)
                    ->transform(fn (string $checkedSqlMode) => Str::of($checkedSqlMode)->lower())
                    ->diff($sqlModes);
            });
        if ($diffSqlModes->isNotEmpty()) {
            return $this->failing("`sql_mode` is not set to `{$diffSqlModes->implode('、')}`. Please set to them.");
        }

        return $this->ok('The database sql_mode config check passed');
    }

    /**
     * @throws \Exception
     */
    protected function checkTimeZone(): array
    {
        if ('mysql' !== config('database.default')) {
            return $this->warning('This check is only available for MySQL.');
        }

        $dbTimeZone = DB::select("SHOW VARIABLES LIKE 'time_zone' ")[0]->Value;
        Str::of($dbTimeZone)->lower()->is('system') and $dbTimeZone = DB::select("SHOW VARIABLES LIKE 'system_time_zone' ")[0]->Value;

        if ($dbTimeZone) {
            $dbDateTime = (new \DateTimeImmutable('now', new \DateTimeZone($dbTimeZone)))->format('YmdH');
            $appDateTime = (new \DateTimeImmutable('now', new \DateTimeZone($appTimezone = config('app.timezone'))))->format('YmdH');
            if ($dbDateTime !== $appDateTime) {
                return $this->failing("The database timezone(`$dbTimeZone`) is not equal to app timezone(`$appTimezone`).");
            }
        }

        return $this->ok('The database timezone config check passed');
    }

    protected function checkPing(?string $url = null): array
    {
        $url = $url ?: config('app.url');

        $response = Http::get($url);
        if ($response->failed()) {
            return $this->failing("Could not connect to the application: `{$response->body()}`");
        }

        return $this->ok();
    }

    protected function checkPhpVersion(): array
    {
        if (version_compare(PHP_VERSION, '7.3.0', '<')) {
            return $this->failing('PHP version is less than 7.3.0.');
        }

        return $this->ok('PHP version check passed');
    }

    protected function checkPhpExtensions(): array
    {
        $extensions = [
            'curl',
            'gd',
            'mbstring',
            'openssl',
            'pdo',
            'pdo_mysql',
            'xml',
            'zip',
            // 'swoole',
        ];

        /** @var Collection $missingExtensions */
        $missingExtensions = collect($extensions)
            ->reduce(fn (Collection $missingExtensions, $extension) => $missingExtensions->when(! \extension_loaded($extension), fn (Collection $missingExtensions) => $missingExtensions->add($extension)), collect());

        if ($missingExtensions->isNotEmpty()) {
            return $this->failing("The following PHP extensions are missing: `{$missingExtensions->implode('、')}`.");
        }

        return $this->ok('The following PHP extensions check passed');
    }

    protected function checkDiskSpace(): array
    {
        $freeSpace = disk_free_space(base_path());
        $diskSpace = sprintf('%.1f', $freeSpace / (1024 * 1024));
        if ($diskSpace < 100) {
            return $this->failing("The disk space is less than 100MB: `$diskSpace`.");
        }

        $diskSpace = sprintf('%.1f', $freeSpace / (1024 * 1024 * 1024));
        if ($diskSpace < 1) {
            return $this->warning("The disk space is less than 1GB: `$diskSpace`.");
        }

        return $this->ok('The disk space check passed');
    }

    protected function checkMemoryLimit(int $limit = 128): array
    {
        $inis = collect(ini_get_all())->filter(fn ($value, $key) => str_contains($key, 'memory_limit'));

        if ($inis->isEmpty()) {
            return $this->failing('The memory limit is not set.');
        }

        $localValue = $inis->first()['local_value'];
        if ($localValue < $limit) {
            return $this->failing("The memory limit is less than {$limit}M: `$localValue`.");
        }

        return $this->ok('The memory limit check passed');
    }

    protected function checkQueue(): array
    {
        if (! Queue::connected()) {
            return $this->failing('The queue is not connected.');
        }

        return $this->ok('The queue limit check passed');
    }

    protected function failing($description = ''): array
    {
        return $this->wrapState(self::STATE_FAILING, $description);
    }

    protected function warning($description = ''): array
    {
        return $this->wrapState(self::STATE_WARNING, $description);
    }

    protected function ok($description = ''): array
    {
        return $this->wrapState(self::STATE_OK, $description);
    }

    private function wrapState($state, $description): array
    {
        return ['state' => $state, 'description' => $description];
    }
}
