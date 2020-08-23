<?php

namespace Codino\EloquentHistory;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class HistoryTracker
{
    private static $ignore = [];

    public static function getHistoryOf($model, $columns)
    {
        // build the final state of the model.
        $base = [ 'created_at' => (string) $model->updated_at];
        foreach ($columns as $_col) {
            $base[$_col] = $model->$_col;
        }

        $updates = [];
        $changes = self::queryChanges($model);
        foreach ($changes as $i => $change) {
            if (in_array($change->col_name, $columns)) { // optimization
                $base[$change->col_name] = $change->value;
            }
            $changeId = $change->change_id;
            $updates[$changeId] = $base;
            $updates[$changeId]['user_id'] = $change->user_id;
            $updates[$changeId]['created_at'] = $change->created_at;
        }

        return $updates;
    }

    public static function track($model, $except = [])
    {
        self::$ignore[$model] = $except;

        $model::updating(function ($model) {
            self::saveChanges($model, $model->getDirty());
        });

        $model::deleting(function ($model) {
            self::saveChanges($model, $model->getAttributes());
        });

        self::commitChanges($model);
    }

    public static function saveChanges(Model $model, $attrs)
    {
        DB::beginTransaction();
        $attrs = Arr::except($attrs, self::$ignore[get_class($model)]);
        if (! $attrs) {
            return null;
        }
        $id = self::saveDataChanges($model);
        self::saveChangedAttrs($attrs, $model, $id);
    }

    private static function saveChangedAttrs($attrs, Model $model, int $id)
    {
        $data = [];
        foreach ($attrs as $key => $val) {
            $data[] = [
                'col_name' => $key,
                'value' => $model->getOriginal($key),
                'change_id' => $id
            ];
        }
        DB::table('data_changes')->insert($data);
    }

    private static function saveDataChanges(Model $model)
    {
        $id = DB::table('data_changes_meta')->insertGetId([
            'created_at' => now(),
            'user_id' => auth()->id(),
            'row_id' => $model->id,
            'table_name' => $model->getTable(),
            'ip' => request()->ip(),
            'route' => request()->route()->getName() ?? request()->route()->uri(),
        ]);

        return $id;
    }

    private static function commitChanges($model)
    {
        $model::updated(function () {
            DB::commit();
        });

        $model::deleted(function () {
            DB::commit();
        });
    }

    private static function queryChanges($model)
    {
        return DB::table('data_changes_meta')
            ->join('data_changes_meta', 'data_changes.id', '=', 'change_id')
            ->where('row_id', $model->id)
            ->where('table_name', $model->getTable())
            ->orderBy('data_changes.id', 'DESC')
            ->get();
    }
}
