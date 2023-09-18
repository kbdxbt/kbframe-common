<?php

declare(strict_types=1);

namespace Modules\Common\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class ClearAllCommand extends Command
{
    /**
     * The console command name.
     */
    protected $name = 'clear:all';

    /**
     * The console command description.
     */
    protected $description = 'clear optimized all.';

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
        if (app()->isProduction() && ! $this->option('force')) {
            return self::INVALID;
        }

        $resourceUsage = catch_resource_usage(function (): void {
            $this->call('config:clear');
            $this->call('event:clear');
            $this->call('route:clear');
            $this->call('view:clear');
            $this->call('optimize:clear');
            $this->call('clear-compiled');
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
