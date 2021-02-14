<?php

namespace Bfg\Dev;

use Bfg\Dev\Commands\BfgDumpCommand;
use Bfg\Dev\Commands\BfgPackageDiscoverCommand;
use Bfg\Dev\Commands\DumpAutoload;
use Bfg\Dev\Commands\ComponentMakeCommand;
use Bfg\Dev\Commands\RepositoryMakeCommand;
use Bfg\Dev\Commands\RequestMakeCommand;
use Bfg\Dev\Commands\SpeedTestCommand;
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
        BfgDumpCommand::class,
        SpeedTestCommand::class,
        RepositoryMakeCommand::class
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
        if ($this->app->runningInConsole()) {

            $this->app->extend('command.package.discover', function () {
                return new BfgPackageDiscoverCommand;
            });
            $this->app->extend('command.component.make', function () {
                return new ComponentMakeCommand(app('files'));
            });
            $this->app->extend('command.request.make', function () {
                return new RequestMakeCommand(app('files'));
            });
        }

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

