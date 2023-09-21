<?php

namespace Modules\Common\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Route;
use Jiannei\Response\Laravel\Providers\LaravelServiceProvider;
use Modules\Common\Http\Controllers\UploadController;
use Modules\Common\Providers\CommonServiceProvider;
use Modules\Core\Providers\CoreServiceProvider;
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
            ->create('http_log', static function (Blueprint $blueprint): void {
                $blueprint->bigIncrements('id');
                $blueprint->string('method');
                $blueprint->string('url');
                $blueprint->integer('response_code');
                $blueprint->integer('request_time');
                $blueprint->integer('response_time');
                $blueprint->mediumText('request_header');
                $blueprint->mediumText('request_param');
                $blueprint->mediumText('response_header');
                $blueprint->mediumText('response_body');
                $blueprint->string('ip');
                $blueprint->string('duration');
                $blueprint->string('request_id');
                $blueprint->json('ext');
                $blueprint->timestamps();
                $blueprint->softDeletes();
            });
    }

    protected function getPackageProviders($app): array
    {
        return [
            LaravelModulesServiceProvider::class,
            CommonServiceProvider::class,
            CoreServiceProvider::class,
            LaravelServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        config()->set('services.signer.default.secret', '4d6qRiYGLhWOKiI8');

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
        Route::post('upload_image', [UploadController::class, 'image'])
            ->middleware(['log.http', sprintf('verify.signature:%s', config('services.signer.default.secret'))]);
    }
}
