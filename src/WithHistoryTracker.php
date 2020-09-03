<?php

namespace Imanghafoori\EloquentHistory;

trait WithHistoryTracker
{
    protected static function bootWithHistoryTracker()
    {
        HistoryTracker::track(static::class, static::$historyTrackerExceptions ?? []);
    }
}
