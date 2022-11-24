# laravel-trackable-jobs

A simple package to keep track of your Laravel Queue jobs.

## Requirements

- PHP 8.1+
- Laravel 9+

---

## Installing

```shell
$ composer require hareland/laravel-trackable-jobs
```

The ServiceProvider should be discovered automatically.

---

## Usage

### Constructing new jobs

```php
SomeJob::dispatch($trackableModel);
```

### Custom constructors

```php
use Hareland\Trackable\Traits\Trackable;

class CustomJob
{
    use Trackable {
        Trackable::__construct as traitConstructor
    }
    
    
    public function __construct(
        protected int $someNumber,
        \Illuminate\Database\Eloquent\Model $trackable,
    ){
        $this->traitConstructor($trackable);
        
        //The model with the status attached is now available using:
        $this->getTrackable();//Model
        $this->getTrackedJobEnvelope(); //This si the model storing the progress.
    }
}
```

### Models

```php

<?php
//App/Models/Letter.php

namespace App\Models;

use Hareland\Trackable\Models\TrackableJobEnvelope;
use Hareland\Trackable\Traits\Trackable;
use Illuminate\Database\Eloquent\Model;

class Letter extends Model
{
    public function progress(): MorphTo|TrackableJobEnvelope
    {
        return $this->morphTo('trackable', TrackableJobEnvelope::class);
    }
}
```

### Jobs

```php
<?php
//App/Jobs/SendLetterJob.php

namespace App\Jobs;


use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Hareland\Trackable\Traits\Trackable;
use Illuminate\Support\Facades\Http;
use Illuminate\Contracts\Queue\Job;
use Carbon\Carbon;



class SendLetterJob extends Job implements ShouldQueue
{
    use Trackable;
    use Dispatchable;
    
    //Required: (For how this is handled @see \Hareland\Trackable\Jobs\Middleware)
    public function handle()
    {
        Http::get('https://jsonapi.example/long-operation');//...
    }
    
    
    //Optional:
    public function success(mixed $response): void
    {
        //Handle a successful run 
        $this->getTrackable(); //This is the constructor passed
        $this->markAsFinished();
    }
   
    //Optional:
    public function fail(\Throwable $throwable)
    {
        //If for any reason the handle method throw an exception, it will be catched by "fail/failed" so you can deal with it.
        //If you use failed instead of fail, you can do cleanup
        $this->markAsFailed($throwable);
    } 
    
    //Optional:
    public function before(Job $job, callable $next): void
    {
          if(Carbon::now()->isBefore(Carbon::yesterday())){
          //Of course, it is, but we can conditionally fail the job before it's handle method is called
                $this->fail(new \RuntimeException('Yesterday is before today... '));
          }
    }
    
    //Optional:
    public function after(Job $job, callable $next): void
    {
        //If you want to call something after the success function has been called.
    }
    
    
   
}
```

---

## Extending this package

```php
<?php

namespace App\Providers;


class MyServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(\Hareland\Trackable\Middleware::class, fn()=> new CustomJobMiddleware);
    }
}
```