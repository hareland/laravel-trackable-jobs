<?php

namespace Hareland\Trackable;


use Hareland\Trackable\Contracts\Middleware;
use Hareland\Trackable\Jobs\Middleware\Tracked;
use Illuminate\Support\ServiceProvider;

class LaravelTrackableJobServiceProvider extends ServiceProvider
{
    protected static array $manifest = [];

    public function register(): void
    {
        $this->app->bind(Middleware::class, fn() => new Tracked);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->mergeConfigFrom(__DIR__ . '/../config/trackable-jobs.php', 'trackable-jobs');
    }

}
