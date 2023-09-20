<?php

declare(strict_types=1);

namespace Modules\Common\Console;

use Illuminate\Contracts\Console\Isolatable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * @note command php artisan app:init --isolated # 单例执行
 */
class AppInitCommand extends HealthCheckCommand implements Isolatable
{
    /**
     * The console command name.
     */
    protected $name = 'app:init';

    /**
     * The console command description.
     */
    protected $description = 'init project';

    protected array $onlyCheck = [
        'checkPhpVersion',
        'checkPhpExtensions',
        'checkDatabaseVersion',
        'checkDatabase',
    ];

    public function handle(): int
    {
        // todo composer install?
        if ($this->environmentCheck()) {
            $this->output->success('environment check passed');
        } else {
            return self::FAILURE;
        }

        $this->execCommands();

        return self::SUCCESS;
    }

    protected function environmentCheck()
    {
        return collect((new \ReflectionObject($this))->getMethods(\ReflectionMethod::IS_PROTECTED | \ReflectionMethod::IS_PRIVATE))
            ->filter(fn (\ReflectionMethod $method) => Str::of($method->name)->startsWith('check') && Str::of($method->name)->contains($this->onlyCheck))
            ->sortBy(function ($method) {
                $index = array_search($method->name, $this->onlyCheck, true);

                return false !== $index ? $index : \count($this->onlyCheck);
            })->pipe(function (Collection $methods) {
                $methods->map(function ($method) use (&$checks): void {
                    $result = \call_user_func([$this, $method->name]);

                    $checks[] = [
                        'resource' => Str::of($method->name)->replaceFirst('check', ''),
                        'state' => $result['state'],
                        'message' => $result['description'],
                    ];
                });

                return collect($checks);
            })
            ->filter(fn ($check) => $check['state'] !== self::STATE_OK)
            ->whenNotEmpty(function (Collection $notOkChecks) {
                foreach ($notOkChecks as $check) {
                    $this->components->error(sprintf('%s:[%s]', $check['resource'], $check['message']));
                }

                return $notOkChecks;
            })->whenEmpty(fn (Collection $notOkChecks) => $notOkChecks)->isEmpty();
    }

    protected function execCommands(): bool
    {
        $resourceUsage = catch_resource_usage(function (): void {
            $this->call('migrate');
            $this->call('db:seed');
            $this->call('key:generate');
            $this->call('jwt:secret');
            $this->call('storage:link');
        });

        $this->output->success('Execute commands '.$resourceUsage);

        return true;
    }
}
