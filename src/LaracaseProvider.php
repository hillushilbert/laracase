<?php

namespace Hillus\Laracase;

use Hillus\Laracase\Commands\CrudApiCommand;
use Hillus\Laracase\Commands\CrudApiControllerCommand;
use Hillus\Laracase\Commands\CrudCommand;
use Hillus\Laracase\Commands\CrudControllerCommand;
use Hillus\Laracase\Commands\CrudLangCommand;
use Hillus\Laracase\Commands\CrudMigrationCommand;
use Hillus\Laracase\Commands\CrudModelCommand;
use Hillus\Laracase\Commands\CrudViewCommand;
use Hillus\Laracase\Commands\GridCommand;
use Hillus\Laracase\Commands\GridControllerCommand;
use Hillus\Laracase\Commands\GridViewCommand;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;


class LaracaseProvider extends BaseServiceProvider
{
    /**
     * The prefix to use for register/load the package resources.
     *
     * @var string
     */
    protected $pkgPrefix = 'laracase';


    

    /**
     * Register the package services.
     *
     * @return void
     */
    public function register()
    {
        // Bind a singleton instance of the AdminLte class into the service
        // container.

        $this->app->singleton(AdminLte::class, function (Container $app) {
            return new AdminLte(
                $app['config']['adminlte.filters'],
                $app['events'],
                $app
            );
        });
    }

    /**
     * Bootstrap the package's services.
     *
     * @return void
     */
    public function boot(Factory $view, Dispatcher $events, Repository $config)
    {
        $this->loadViews();
        $this->loadConfig();
        $this->registerCommands();
        $this->loadRoutes();
    }

    /**
     * Load the package views.
     *
     * @return void
     */
    private function loadViews()
    {
        $viewsPath = $this->packagePath('resources/views');
        $this->loadViewsFrom($viewsPath, $this->pkgPrefix);
    }



    /**
     * Load the package config.
     *
     * @return void
     */
    private function loadConfig()
    {
        $configPath = $this->packagePath('config/laracase.php');
        $this->mergeConfigFrom($configPath, $this->pkgPrefix);
    }

    /**
     * Get the absolute path to some package resource.
     *
     * @param  string  $path  The relative path to the resource
     * @return string
     */
    private function packagePath($path)
    {
        return __DIR__."/../$path";
    }

    /**
     * Register the package's artisan commands.
     *
     * @return void
     */
    private function registerCommands()
    {
        $this->commands([
            CrudApiCommand::class,
            CrudApiControllerCommand::class,
            CrudCommand::class,
            CrudControllerCommand::class,
            CrudLangCommand::class,
            CrudMigrationCommand::class,
            CrudModelCommand::class,
            CrudViewCommand::class,
            GridCommand::class,
            GridControllerCommand::class,
            GridViewCommand::class,
        ]);
    }
    

    /**
     * Load the package web routes.
     *
     * @return void
     */
    private function loadRoutes()
    {
        $routesCfg = [
            'as' => "{$this->pkgPrefix}.",
            'prefix' => $this->pkgPrefix,
            'middleware' => ['web'],
        ];

        Route::group($routesCfg, function () {
            $routesPath = $this->packagePath('routes/web.php');
            $this->loadRoutesFrom($routesPath);
        });
    }
}