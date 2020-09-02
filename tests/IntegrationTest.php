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
    public function tracks_created_model_event()
    {
        Route::get('/', function() {});
        $this->get('/');

        HistoryTracker::track(User::class);

        factory(User::class)->create();

        $this->assertEquals(1, DataChangesMeta::count());
        $this->assertEquals(7, DataChange::count());
    }

    /** @test */
    public function tracks_updated_model_event()
    {
        Route::get('/', function() {});
        $this->get('/');

        HistoryTracker::track(User::class);

        $user = factory(User::class)->create();
        $user->age = 10;
        $user->save();

        $this->assertEquals(2, DataChangesMeta::count());
        $this->assertEquals(8, DataChange::count());

        $user->username = 'new username';
        $user->age = 20;
        $user->save();

        $this->assertEquals(3, DataChangesMeta::count());
        $this->assertEquals(10, DataChange::count());
    }

    /** @test */
    public function doesnt_track_updated_model_event_when_updating_bulkly()
    {
        Route::get('/', function() {});
        $this->get('/');

        HistoryTracker::track(User::class);

        $user = factory(User::class)->create();

        User::whereId($user->id)->update(['age' => 10]);

        $this->assertEquals(1, DataChangesMeta::count());
        $this->assertEquals(7, DataChange::count());
    }

    /** @test */
    public function tracks_deleted_model_event()
    {
        Route::get('/', function() {});
        $this->get('/');

        HistoryTracker::track(User::class);

        $user = factory(User::class)->create();

        User::destroy($user->id);

        $this->assertEquals(2, DataChangesMeta::count());
        $this->assertEquals(14, DataChange::count());

        $user2 = factory(User::class)->create();

        $user2->delete();

        $this->assertEquals(4, DataChangesMeta::count());
        $this->assertEquals(28, DataChange::count());
    }
}
