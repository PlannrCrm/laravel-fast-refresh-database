<?php

namespace Plannr\Laravel\FastRefreshDatabase\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Plannr\Laravel\FastRefreshDatabase\Traits\FastRefreshDatabase;

class TestCase extends BaseTestCase
{
    use FastRefreshDatabase;

    /**
     * Define database migrations.
     *
     * @return void
     */
    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
    }
}
