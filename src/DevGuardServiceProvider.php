<?php

namespace Niduranga\DevGuard;

use Illuminate\Support\ServiceProvider;
use Niduranga\DevGuard\Commands\GenerateTestCommand;

class DevGuardServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Commands\InstallCommand::class,
                GenerateTestCommand::class,
            ]);
        }

        $this->publishes([
            __DIR__.'/../config/dev-guard.php' => config_path('dev-guard.php'),
        ], 'config');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/dev-guard.php', 'dev-guard');
    }
}