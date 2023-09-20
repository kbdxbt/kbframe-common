<?php

declare(strict_types=1);

namespace Modules\Common\Providers;

use Illuminate\Console\Command;
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
use Illuminate\Notifications\ChannelManager;
use Illuminate\Routing\ResponseFactory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;
use Modules\Common\Channels\NotifyChannel;
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
use Symfony\Component\Finder\Finder;

class CommonServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Common';

    protected string $moduleNameLower = 'common';

    /**
     * Boot the application events.
     *
     * @throws \ReflectionException
     */
    public function boot(): void
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));

        $this->registerGlobalFunctionsFrom();
        //$this->registerMacros();
        $this->registerCommands();
        $this->extendValidator();
        $this->registerNotificationChannel();
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Register translations.
     */
    public function registerTranslations(): void
    {
        $langPath = resource_path('lang/modules/'.$this->moduleNameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->moduleNameLower);
            $this->loadJsonTranslationsFrom($langPath);
        } else {
            $this->loadTranslationsFrom(module_path($this->moduleName, 'Resources/lang'), $this->moduleNameLower);
            $this->loadJsonTranslationsFrom(module_path($this->moduleName, 'Resources/lang'));
        }
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
                    resolve_class_from($file, base_path('Modules'), config('modules.namespace'))
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
        foreach (Module::scan() as $module) {
            $rulePath = $module->getPath().'/Rules';
            if (! is_dir($rulePath)) {
                continue;
            }

            foreach ((new Finder())->in($rulePath)->files() as $ruleFile) {
                $ruleClass = Str::ucfirst(str_replace(
                    ['/', '.php'],
                    ['\\', ''],
                    Str::after($ruleFile->getRealPath(), realpath(base_path()).\DIRECTORY_SEPARATOR)
                ));

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

    protected function registerGlobalFunctionsFrom(): void
    {
        collect(glob(__DIR__.'/../Support/*helpers.php'))
            ->each(function ($file): void {
                require_once $file;
            });
    }

    protected function registerNotificationChannel(): void
    {
        $this->app->make(ChannelManager::class)->extend('notify', fn ($app) => $app->make(NotifyChannel::class));
    }
}
