<?php

namespace Plannr\Laravel\FastRefreshDatabase\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Plannr\Laravel\FastRefreshDatabase\FastRefreshDatabaseServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            FastRefreshDatabaseServiceProvider::class,
        ];
    }
}
