<?php

namespace Imanghafoori\EloquentHistory\Tests;

use Illuminate\Support\Facades\Route;
use Imanghafoori\EloquentHistory\HistoryTracker;
use Imanghafoori\EloquentHistory\Tests\Stubs\Models\DataChange;
use Imanghafoori\EloquentHistory\Tests\Stubs\Models\DataChangesMeta;
use Imanghafoori\EloquentHistory\Tests\Stubs\Models\User;
use ReflectionClass;

class IntegrationTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();

        Route::get('/', function() {});
        $this->get('/');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->untrackAllModels();
    }

    /** @test */
    public function tracks_created_model_event()
    {
        $this->trackUser();

        $this->createNewUser();

        $this->assertEquals(1, DataChangesMeta::count());
        $this->assertEquals(7, DataChange::count());
    }

    /** @test */
    public function tracks_updated_model_event()
    {
        $this->trackUser();

        $user = $this->createNewUser();
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
        $this->trackUser();

        $user = $this->createNewUser();

        $this->assertEquals(1, DataChangesMeta::count());
        $this->assertEquals(7, DataChange::count());

        User::whereId($user->id)->update(['age' => 10]);

        $this->assertEquals(1, DataChangesMeta::count());
        $this->assertEquals(7, DataChange::count());
    }

    /** @test */
    public function tracks_deleted_model_event()
    {
        $this->trackUser();

        $user = $this->createNewUser();

        User::destroy($user->id);

        $this->assertEquals(2, DataChangesMeta::count());
        $this->assertEquals(14, DataChange::count());

        $user2 = $this->createNewUser();

        $user2->delete();

        $this->assertEquals(4, DataChangesMeta::count());
        $this->assertEquals(28, DataChange::count());
    }

    /** @test */
    public function doesnt_track_excepted_columns()
    {
        $this->trackUser(['age']);

        $user = $this->createNewUser();

        $this->assertEquals(1, DataChangesMeta::count());
        $this->assertEquals(6, DataChange::count());

        // since this field is not being tracked, it shouldn't submit any new history
        $user->age = 11;
        $user->save();

        $this->assertEquals(1, DataChangesMeta::count());
        $this->assertEquals(6, DataChange::count());

        $user->email = 'iman@laravel.com';
        $user->save();

        $this->assertEquals(2, DataChangesMeta::count());
        $this->assertEquals(7, DataChange::count());

        $user->delete();

        $this->assertEquals(3, DataChangesMeta::count());
        $this->assertEquals(13, DataChange::count());
    }

    /** @test */
    public function doesnt_track_model_events_multiple_times()
    {
        $this->trackUser();
        $this->trackUser();
        $this->trackUser();

        $this->createNewUser();

        $this->assertEquals(1, DataChangesMeta::count());
        $this->assertEquals(7, DataChange::count());
    }

    /** @test */
    public function excepted_attributes_can_be_overwritten()
    {
        $this->trackUser();
        $this->trackUser(['age']);

        $this->createNewUser();

        $this->assertEquals(1, DataChangesMeta::count());
        $this->assertEquals(6, DataChange::count());
    }

    private function trackUser($exceptions = [])
    {
        HistoryTracker::track(User::class, $exceptions);
    }

    private function createNewUser()
    {
        return factory(User::class)->create();
    }

    private function untrackAllModels(): void
    {
        (new ReflectionClass(HistoryTracker::class))->setStaticPropertyValue('ignore', []);
    }
}
