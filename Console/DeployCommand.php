<?php

declare(strict_types=1);

namespace Modules\Common\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;

class DeployCommand extends Command
{
    /**
     * The console command name.
     */
    protected $name = 'deploy';

    /**
     * The console command description.
     */
    protected $description = 'deploy project';

    public function handle(): int
    {
        Process::fromShellCommandline('git reset --hard HEAD && git pull')
            ->mustRun(function (string $type, string $line): void {
                $this->output->write($line);
            });

        if ($this->option('composer')) {
            Process::fromShellCommandline('composer install --no-dev')
                ->mustRun(function (string $type, string $line): void {
                    $this->output->write($line);
                });
        }

        if ($this->option('migrate')) {
            $this->call('migrate');
        }

        return self::SUCCESS;
    }

    /**
     * Get the console command options.
     */
    protected function getOptions(): array
    {
        return [
            ['composer', 'c', InputOption::VALUE_NONE, 'Flag to composer install', null],
            ['migrate', 'm', InputOption::VALUE_NONE, 'Flag to migrate table data', null],
            ['force', 'f', InputOption::VALUE_NONE, 'Flag to force optimize', null],
        ];
    }
}
