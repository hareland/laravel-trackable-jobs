<?php

namespace Hareland\Trackable;


use Illuminate\Support\ServiceProvider;

class LaravelTrackableJobServiceProvider extends ServiceProvider
{
    protected static array $manifest = [];

    public function register(): void
    {

    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->mergeConfigFrom(__DIR__ . '/../config/trackable-jobs.php', 'trackable-jobs');
    }

}
