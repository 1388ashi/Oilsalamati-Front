<?php

namespace Modules\Core\Providers;

//use Shetabit\Shopit\Modules\Core\Providers\CoreServiceProvider as BaseCoreServiceProvider;


use App\Providers\AppServiceProvider;
use CyrildeWit\EloquentViewable\Visitor;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Modules\Core\Classes\CoreSettings;
use Modules\Core\Classes\Views;
use Modules\Core\Console\SitemapCommand;
use Modules\Core\Http\Middleware\CheckUserStatus;
use Shetabit\Shopit\Modules\Core\Console\Faker;
use Shetabit\Shopit\Modules\Core\Console\FreshAppCommand;
use Shetabit\Shopit\Modules\Core\Console\InstallApp;
use Shetabit\Shopit\Modules\Core\Console\MoveDatabase;
use Shetabit\Shopit\Modules\Core\Console\UpdateApp;
use Shetabit\Shopit\Modules\Core\Providers\RouteServiceProvider;

class CoreServiceProvider extends ServiceProvider
{
    /**
     * @var string $moduleName
     */
    protected $moduleName = 'Core';

    /**
     * @var string $moduleNameLower
     */
    protected $moduleNameLower = 'core';

    // Dump queries for performance check
    protected $checkQueries = false;

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerTranslations();
        if (!AppServiceProvider::$configurationIsCached || AppServiceProvider::$runningInConsole) {
            $this->registerConfig();
        }

        if (class_exists(CheckUserStatus::class)) {
            /** @var Router $router */
            $router = $this->app['router'];
            $router->pushMiddlewareToGroup('api', CheckUserStatus::class);
        }

        $this->registerViews();

        // $this->registerFactories();
        if (AppServiceProvider::$runningInConsole) {
            if (!empty($_SERVER['argv'][1])
                && ($_SERVER['argv'][1] == 'migrate:fresh' || $_SERVER['argv'][1] == 'module:seed')
            ){
                Artisan::command('cache:clear', fn() => true);
                Artisan::command('config:cache', fn() => true);
                echo "Application cache cleared!".PHP_EOL;
                echo "Application config cached!".PHP_EOL;
            }

            $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));
        }

        Schema::defaultStringLength(191);

        $this->loadMarcos();
        if ($this->checkQueries) {
            $this->checkQueries();
        }
        $this->loadCommands();
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/' . $this->moduleNameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->moduleNameLower);
        } else {
            $this->loadTranslationsFrom(module_path($this->moduleName, 'Resources/lang'), $this->moduleNameLower);
        }
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            module_path($this->moduleName, 'Config/config.php') => config_path($this->moduleNameLower . '.php'),
        ], 'config');
        $this->mergeConfigFrom(
            module_path($this->moduleName, 'Config/config.php'), $this->moduleNameLower
        );
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/' . $this->moduleNameLower);

        $sourcePath = module_path($this->moduleName, 'Resources/views');

        $this->publishes([
            $sourcePath => $viewPath
        ], ['views', $this->moduleNameLower . '-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->moduleNameLower);
        $this->loadViewsFrom([
            base_path("vendor/shetabit/shopit/src/Modules/Core/Resources/views")
        ], "basecore");
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (\Config::get('view.paths') as $path) {
            if (is_dir($path . '/modules/' . $this->moduleNameLower)) {
                $paths[] = $path . '/modules/' . $this->moduleNameLower;
            }
        }
        return $paths;
    }

    public function loadMarcos()
    {
        require __DIR__ . '/../macros.php';
        if ($this->app->environment() === 'production' || $this->app->runningInConsole()) {
            require __DIR__ . '/../macros-dev.php';
        }
    }

    public function checkQueries()
    {
        DB::listen(function ($query) {
            dump($query->sql);
        });
    }

    public function loadCommands()
    {
        \Artisan::command('push', function () {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
            ])->get('http://api-atlas.cheshbaste.com/pull.php?key=backend&p=2');
            echo $response;
        });

        $this->commands([
            SitemapCommand::class,
            Faker::class,
            InstallApp::class,
            UpdateApp::class,
            FreshAppCommand::class,
            MoveDatabase::class
        ]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        if (!AppServiceProvider::$routesAreCached || AppServiceProvider::$runningInConsole) {
            $this->app->register(RouteServiceProvider::class);
        }
        $this->app->singleton(CoreSettings::class, CoreSettings::class);
        //Bind Sms
        $this->app->bind('KavenegarApi', function ($app) {
            $apiKey = config('kavenegar.apikey');
            return new \Kavenegar\KavenegarApi($apiKey);
        });
        $this->app->bind('Sms', function ($app) {
            return new \Modules\Core\Helpers\Sms($app->make('KavenegarApi'));
        });
        if (class_exists(Visitor::class)) {
            $this->app->bind(\CyrildeWit\EloquentViewable\Contracts\Views::class, Views::class);
        }
    }

    /**
     * Register an additional directory of factories.
     *
     * @return void
     */
    public function registerFactories()
    {
        if (!app()->environment('production') && $this->app->runningInConsole()) {
            app(Factory::class)->load(module_path($this->moduleName, 'Database/factories'));
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}
