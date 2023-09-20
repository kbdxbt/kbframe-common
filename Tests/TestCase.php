<?php

namespace Modules\Common\Tests;

use Illuminate\Database\Schema\Blueprint;
use Modules\Common\Providers\CommonServiceProvider;
use Nwidart\Modules\LaravelModulesServiceProvider;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpDatabase();
    }

    protected function setUpDatabase(): void
    {
        $this->app['db']
            ->connection()
            ->getSchemaBuilder()
            ->create('members', static function (Blueprint $blueprint): void {
                $blueprint->bigIncrements('id');
                $blueprint->string('name');
                $blueprint->string('email')->unique();
                $blueprint->timestamp('email_verified_at')->nullable();
                $blueprint->string('password');
                $blueprint->rememberToken();
                $blueprint->timestamps();
            });
    }

    protected function getPackageProviders($app): array
    {
        return [
            LaravelModulesServiceProvider::class,
            CommonServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        config()->set('app.debug', true);

        $app['config']->set('modules.paths.modules', __DIR__.'/../../');
    }

    protected function defineRoutes($router): void
    {
    }
}
