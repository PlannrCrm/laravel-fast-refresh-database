<?php

namespace Plannr\Laravel\FastRefreshDatabase;

use Illuminate\Support\ServiceProvider;

class FastRefreshDatabaseServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->commands([
            Commands\DeleteChecksum::class,
        ]);
    }
}
