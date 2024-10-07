<?php

namespace Svr\Logs;

use Illuminate\Support\ServiceProvider;

class LogsServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function boot()
    {

        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
            $this->publishes([__DIR__.'/../resources/lang' => resource_path('lang')], 'open-admin-lang');
        }

        LogsManager::boot();
    }

}
