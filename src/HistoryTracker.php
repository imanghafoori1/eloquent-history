<?php

namespace Imanghafoori\EloquentHistory;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class HistoryTracker
{
    private static $ignore = [];

    public static function hasEverHad($modelId, $colName, $value, $tableName)
    {
        $row = DB::table($tableName)->where([
            'id' => $modelId,
            $colName => $value,
        ])->first();

        return $row ?: self::getTable()->where([
            'col_name' => $colName,
            'row_id' => $modelId,
            'table_name' => $tableName,
            'value' => $value,
        ])->first();
    }

    public static function getChanges($model, array $cols)
    {
        return self::getTable()
            ->where(['table_name' => $model->getTable(), 'row_id' => $model->id])
            ->whereIn('col_name', $cols)
            ->orderBy('data_changes.id', 'DESC')
            ->get();
    }

    public static function getHistoryOf($model, $columns, $importantCols = [])
    {
        // build the final state of the model.
        $base = [ 'created_at' => (string) $model->updated_at];
        foreach ($columns as $_col) {
            $base[$_col] = $model->$_col;
        }

        $updates = [];
        $changes = self::queryChanges($model);
        foreach ($changes as $i => $change) {
            if ($importantCols && in_array($change->col_name, $importantCols)) {
                if (in_array($change->col_name, $columns)) { // optimization
                    $base[$change->col_name] = $change->value;
                }
                $changeId = $change->change_id;
                $updates[$changeId] = $base;
                $updates[$changeId]['c__user_id'] = $change->user_id;
                $updates[$changeId]['c__created_at'] = $change->created_at;
            }
        }

        return $updates;
    }

    public static function track($model, $except = [])
    {
        // We avoid setting listeners more than once.
        if (array_key_exists($model, self::$ignore)) {
            // to override $ignored columns.
            self::$ignore[$model] = $except;
            return null;
        }

        self::$ignore[$model] = $except;

        $model::updating(function ($model) {
            self::saveChanges($model, $model->getDirty());
        });

        $saver = function ($model) {
            self::saveChanges($model, $model->getAttributes());
        };

        $model::created($saver);
        $model::deleting($saver);

        self::commitChanges($model);
    }

    public static function saveChanges(Model $model, $attrs)
    {
        DB::beginTransaction();
        $attrs = Arr::except($attrs, self::$ignore[get_class($model)]);
        if ($attrs) {
            $id = self::saveDataChanges($model);
            self::saveChangedAttrs($attrs, $id);
        }
    }

    private static function saveChangedAttrs($attrs, int $id)
    {
        $data = [];
        foreach ($attrs as $key => $val) {
            $data[] = [
                'col_name' => $key,
                'value' => $val,
                'change_id' => $id
            ];
        }
        DB::table('data_changes')->insert($data);
    }

    private static function saveDataChanges(Model $model)
    {
        $uid = auth()->id() ?: 0;
        $rowId = $model->id;
        $table = $model->getTable();
        $ip = request()->ip();
        $route = request()->route()->getName() ?? request()->route()->uri();

        return self::saveMetaData(now(), $uid, $rowId, $table, $ip, $route);
    }

    private static function commitChanges($model)
    {
        $commit = function () {
            DB::commit();
        };

        $model::updated($commit);
        $model::created($commit);
        $model::deleted($commit);
    }

    private static function queryChanges($model)
    {
        return self::getTable()
            ->where('row_id', $model->id)
            ->where('table_name', $model->getTable())
            ->orderBy('data_changes.id', 'DESC')
            ->get();
    }

    private static function getTable()
    {
        return DB::table('data_changes')->join('data_changes_meta', 'data_changes_meta.id', '=', 'change_id');
    }

    private static function saveMetaData(Carbon $time, $uid, $rowId, $table, $ip, $route)
    {
        return DB::table('data_changes_meta')->insertGetId([
            'created_at' => $time,
            'user_id' => $uid,
            'row_id' => $rowId,
            'table_name' => $table,
            'ip' => $ip,
            'route' => $route,
        ]);
    }
}
