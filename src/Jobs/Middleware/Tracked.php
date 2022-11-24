<?php

namespace Hareland\Trackable\Jobs\Middleware;

use Hareland\Trackable\Contracts\Middleware;

use Throwable;

class Tracked implements Middleware
{
    public function handle($job, callable $next)
    {
        $this->started($job, $next);

        try {
            $response = call_user_func($next, $job);

            $this->completed($job, $response);
        } catch (Throwable $e) {
            $this->failed($job, $next, $e);
        }
    }

    public function started($job, callable $next): void
    {
        if (method_exists($job, 'before')) {
            $job->before($job, $next);
        }
    }

    public function completed($job, mixed $response): void
    {
        if (method_exists($job, 'success')) {
            $job->success($response);
        }
        if (method_exists($job, 'after')) {
            $job->after($job, $response);
        }
    }

    public function failed($job, callable $next, Throwable $exception): void
    {
        if (method_exists($job, 'fail')) {
            $job->fail($exception);
        }
    }
}
