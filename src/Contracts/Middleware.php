<?php

namespace Hareland\Trackable\Contracts;


use Illuminate\Contracts\Queue\Job;

interface Middleware
{
    public function handle(Job $job, callable $next);

    public function started(Job $job, callable $next): void;

    public function failed(Job $job, callable $next, \Throwable $exception): void;

    public function completed(Job $job, mixed $response): void;
}