<?php

namespace Imanghafoori\EloquentHistory\Tests;

use Imanghafoori\EloquentHistory\EloquentHistoryServiceProvider;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app)
    {
        return [EloquentHistoryServiceProvider::class];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadMigrationsFrom(__DIR__.'/../src/migration');

        $this->withFactories(__DIR__.'/../database/factories');
    }
}
