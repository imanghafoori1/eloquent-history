<?php

namespace Codino\EloquentHistory;

use Illuminate\Support\ServiceProvider;

class EloquentHistoryServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/migration');
    }
}
