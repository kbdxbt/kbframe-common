<?php

declare(strict_types=1);

namespace Modules\Common\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;

class OptimizeAllCommand extends Command
{
    /**
     * The console command name.
     */
    protected $name = 'optimize:all';

    /**
     * The console command description.
     */
    protected $description = 'Optimize all.';

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
        if (! $this->getLaravel()->isProduction() && ! $this->option('force')) {
            return self::INVALID;
        }

        $resourceUsage = catch_resource_usage(function (): void {
            Process::fromShellCommandline('composer dump-autoload --optimize --ansi')
                ->mustRun(function (string $type, string $line): void {
                    $this->output->write($line);
                });
            $this->call('config:cache');
            $this->call('event:cache');
            $this->call('route:cache');
            $this->call('api:cache');
            $this->call('view:cache');
        });

        $this->output->success($resourceUsage);

        return self::SUCCESS;
    }

    /**
     * Get the console command options.
     */
    protected function getOptions(): array
    {
        return [
            ['force', 'f', InputOption::VALUE_NONE, 'Flag to force optimize', null],
        ];
    }
}
