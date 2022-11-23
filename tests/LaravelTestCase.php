<?php

namespace Hareland\Trackable\Tests;

use Hareland\Trackable\LaravelTrackableJobServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class LaravelTestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate', [
            '--database' => 'testbench',
            //Compensate for being loaded from the vendor package dir.
            '--path' => '../../../../database/migrations/2022_11_23_200356_create_trackable_job_envelopes_table.php',
        ])->run();
    }

    protected function getPackageProviders($app)
    {
        return [
            LaravelTrackableJobServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }
}