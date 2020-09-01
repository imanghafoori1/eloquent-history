<?php

namespace Imanghafoori\EloquentHistory\Tests;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Imanghafoori\EloquentHistory\HistoryTracker;
use Imanghafoori\EloquentHistory\Tests\Stubs\Models\DataChange;
use Imanghafoori\EloquentHistory\Tests\Stubs\Models\DataChangesMeta;
use Imanghafoori\EloquentHistory\Tests\Stubs\Models\User;

class IntegrationTest extends TestCase
{
    /** @test */
    public function tracker_tracks_created_model_event()
    {
        Route::get('/', function() {});

        HistoryTracker::track(User::class);

        $this->get('/');

        factory(User::class)->create();

        $this->assertEquals(1, DataChangesMeta::count());
        $this->assertEquals(7, DataChange::count());
    }
}
