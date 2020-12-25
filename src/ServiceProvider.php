<?php

namespace Bfg\Dev;

use Bfg\Dev\Commands\BfgPackageDiscoverCommand;
use Bfg\Dev\Commands\DumpAutoload;
use Bfg\Dev\Commands\MakeComponentCommand;
use Bfg\Dev\Commands\SpeedTestCommand;
use Illuminate\Console\Application as Artisan;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Foundation\Console\ComponentMakeCommand;
use Illuminate\Foundation\Console\PackageDiscoverCommand;
use Illuminate\Support\ServiceProvider as ServiceProviderIlluminate;

/**
 * Class ServiceProvider
 * @package Bfg\Dev
 */
class ServiceProvider extends ServiceProviderIlluminate
{
    /**
     * @var array
     */
    protected $commands = [
        DumpAutoload::class,
        SpeedTestCommand::class,
        //BfgPackageDiscoverCommand::class
    ];

    /**
     * The application's route middleware.
     * @var array
     */
    protected $routeMiddleware = [

    ];

    /**
     * Bootstrap services.
     *
     * @return void
     * @throws \Exception
     */
    public function boot()
    {
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->extend('command.component.make', function () {
            return new MakeComponentCommand(app('files'));
        });
        $this->app->extend('command.package.discover', function () {
            return new BfgPackageDiscoverCommand;
        });

//        Artisan::starting(function ($artisan) {
//
//            $artisan->resolve(MakeComponentCommand::class);
//        });

//        $this->app->singleton('command.component.make', function ($app) {
//            return new MakeComponentCommand($app['files']);
//        });

        $this->registerRouteMiddleware();

        $this->commands($this->commands);
    }

    /**
     * Register the route middleware.
     *
     * @return void
     */
    protected function registerRouteMiddleware()
    {
        // register route middleware.
        foreach ($this->routeMiddleware as $key => $middleware) {

            app('router')->aliasMiddleware($key, $middleware);
        }
    }
}

