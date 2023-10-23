<?php

declare(strict_types=1);

namespace Modules\Common\Providers;

use Illuminate\Console\Command;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ImplicitRule;
use Illuminate\Contracts\Validation\ValidatorAwareRule;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Query\Grammars\MySqlGrammar;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Grammars\Grammar;
use Illuminate\Http\Request;
use Illuminate\Routing\ResponseFactory;
use Illuminate\Routing\Router;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;
use Modules\Common\Http\Middleware\ProfileJsonResponse;
use Modules\Common\Rules\Rule;
use Modules\Common\Support\Macros\BlueprintMacro;
use Modules\Common\Support\Macros\CollectionMacro;
use Modules\Common\Support\Macros\CommandMacro;
use Modules\Common\Support\Macros\GrammarMacro;
use Modules\Common\Support\Macros\MySqlGrammarMacro;
use Modules\Common\Support\Macros\RequestMacro;
use Modules\Common\Support\Macros\ResponseFactoryMacro;
use Modules\Common\Support\Macros\StringableMacro;
use Modules\Common\Support\Macros\StrMacro;
use Nwidart\Modules\Facades\Module;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Symfony\Component\Finder\Finder;

class CommonServiceProvider extends PackageServiceProvider
{
    protected string $moduleName = 'Common';

    protected string $moduleNameLower = 'common';

    /**
     * The filters base class name.
     *
     * @var array
     */
    protected $middleware = [
        'Common' => [
            'log.http' => 'LogHttp',
            'verify.signature' => 'VerifySignature',
        ],
    ];

    public function configurePackage(Package $package): void
    {
        $package
            ->name('Common')
            ->hasConfigFile(['config', 'notify'])
            ->hasCommands([
                \Modules\Common\Console\AppInitCommand::class,
                \Modules\Common\Console\DeployCommand::class,
                \Modules\Common\Console\HealthCheckCommand::class,
            ]);

        $this->registerMiddleware($this->app['router']);
    }

    /**
     * Boot the application events.
     *
     * @throws \ReflectionException
     */
    public function boot()
    {
        $this->extendValidator();

        return parent::boot();
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);

        parent::register();
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [];
    }

    /**
     * Register config.
     */
    protected function registerConfig(): void
    {
        $this->publishes([
            module_path($this->moduleName, 'Config/config.php') => config_path($this->moduleNameLower.'.php'),
        ], 'config');
        $this->mergeConfigFrom(
            module_path($this->moduleName, 'Config/config.php'),
            $this->moduleNameLower
        );

        $this->publishes([
            module_path($this->moduleName, 'Config/notify.php') => config_path('notify.php'),
        ], 'config');
        $this->mergeConfigFrom(
            module_path($this->moduleName, 'Config/notify.php'),
            'notify'
        );
    }

    /**
     * Register macros.
     */
    protected function registerMacros(): void
    {
        collect(glob(__DIR__.'/../Support/Macros/QueryBuilder/*QueryBuilderMacro.php'))
            ->each(function ($file): void {
                $queryBuilderMacro = $this->app->make(
                    "\\Modules\\{$this->moduleName}\\Support\\Macros\\QueryBuilder\\".pathinfo($file, PATHINFO_FILENAME)
                );
                QueryBuilder::mixin($queryBuilderMacro);
                EloquentBuilder::mixin($queryBuilderMacro);
                Relation::mixin($queryBuilderMacro);
            });

        Blueprint::mixin($this->app->make(BlueprintMacro::class));
        Collection::mixin($this->app->make(CollectionMacro::class));
        Command::mixin($this->app->make(CommandMacro::class));
        Grammar::mixin($this->app->make(GrammarMacro::class));
        MySqlGrammar::mixin($this->app->make(MySqlGrammarMacro::class));
        Request::mixin($this->app->make(RequestMacro::class));
        ResponseFactory::mixin($this->app->make(ResponseFactoryMacro::class));
        Stringable::mixin($this->app->make(StringableMacro::class));
        Str::mixin($this->app->make(StrMacro::class));
    }

    /**
     * Register the filters.
     */
    public function registerMiddleware(Router $router): void
    {
        $this->app->make(Kernel::class)->prependMiddleware(ProfileJsonResponse::class);

        foreach ($this->middleware as $module => $middlewares) {
            foreach ($middlewares as $name => $middleware) {
                $class = "Modules\\{$module}\\Http\\Middleware\\{$middleware}";

                $router->aliasMiddleware($name, $class);
            }
        }
    }

    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Modules\Common\Console\AppInitCommand::class,
                \Modules\Common\Console\DeployCommand::class,
                \Modules\Common\Console\HealthCheckCommand::class,
            ]);
        }
    }

    /**
     * Register rule.
     *
     * @throws \ReflectionException
     */
    protected function extendValidator(): void
    {
        foreach (Module::scan() as $module) { /** @phpstan-ignore-line */
            $rulePath = $module->getPath().'/Rules';
            if (! is_dir($rulePath)) {
                continue;
            }

            foreach ((new Finder())->in($rulePath)->files() as $ruleFile) {
                $ruleClass = '\\Modules\\'.$module->getName().'\\Rules\\'.pathinfo($ruleFile->getFilename(), PATHINFO_FILENAME);

                if (is_subclass_of($ruleClass, Rule::class)
                    && ! (new \ReflectionClass($ruleClass))->isAbstract()) {
                    Validator::{is_subclass_of($ruleClass, ImplicitRule::class) ? 'extendImplicit' : 'extend'}(
                        (string) $ruleClass::name(),
                        function (
                            string $attribute,
                            $value,
                            array $parameters,
                            \Illuminate\Validation\Validator $validator
                        ) use ($ruleClass) {
                            return tap(new $ruleClass(...$parameters), function (Rule $rule) use ($validator): void {
                                $rule instanceof ValidatorAwareRule and $rule->setValidator($validator);
                                $rule instanceof DataAwareRule and $rule->setData($validator->getData());
                            })->passes($attribute, $value);
                        },
                        $ruleClass::localizedMessage()
                    );
                }
            }
        }
    }
}
