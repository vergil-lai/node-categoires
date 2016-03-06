<?php

namespace VergilLai\NodeCategories;

use DB;
use Validator;
use Illuminate\Database\Eloquent\Model;

/**
 * Class NodeCategoryObserver
 * Node Category 模型观察者
 * @property int $id
 * @property int $parent_id
 * @property int $level
 * @property string $name
 * @package VergilLai\NodeCategories
 * @author vergil <vergil@vip.163.com>
 */
class Observer
{
    const SEPARATOR = ',';

    public function creating(Model $model)
    {
    }

    public function created(Model $model)
    {
        $model->parent = isset($model->parent) ? $model->parent : 0;
        $model->syncOriginal();

        if (0 < $model->parent) {
            /**
             * @var Model $parent
             */
            $parent = $model->findOrFail($model->parent);
            $model->node = $parent->node . $model->id . self::SEPARATOR;
            $model->level = $parent->level + 1;
        } else {
            $model->node = self::SEPARATOR . $model->id . self::SEPARATOR;
            $model->level = 1;
        }
        $model->save();
    }

    public function updating(Model $model)
    {
        if ($model->isDirty('parent') && 0 < $model->parent) {
            /**
             * @var Model $parent
             */
            $parent = $model->findOrFail($model->parent);

            if (empty($parent))
                throw new \RuntimeException('Parent node not exists.');

            //檢查新的父類是否自己的子類
            if (false !== strpos($parent->node, $model->node))
                throw new \RuntimeException('The parent can not be your own subclass');

            $model->level = $parent->level + 1;
        }
    }

    public function updated(Model $model)
    {
        if ($model->isDirty('parent')) {
            /**
             * 同步Original数据，是為了清除parent的dirty狀態
             */
            $model->syncOriginal();

            $tableName = $model->getTable();
            $oldNode = $model->getOriginal('node');

            if (0 < $model->parent) {
                /**
                 * @var NodeCategory $parent
                 */
                $parent = $model->find($model->parent);

                $model->node = $parent->node . $model->id . self::SEPARATOR;

                $model->save();

                DB::table($tableName)
                    ->where('node', 'like', $oldNode.'%')
                    ->where('id', '<>', $model->id)
                    ->update([
                        'level' => DB::raw("level + {$parent->level}"),
                        'node' => DB::raw("REPLACE(`node`, '{$oldNode}', '{$model->node}')"),
                    ]);
            } else {    //修改為頂級分類
                $model->level = 1;
                $model->node = self::SEPARATOR . $model->id . self::SEPARATOR;

                $model->save();

                DB::table($tableName)
                    ->where('node', 'like', $oldNode.'%')
                    ->where('id', '<>', $model->id)
                    ->update([
                        'level' => DB::raw("level - {$model->level}"),
                        'node' => DB::raw("CONCAT('{$model->node}', `id`, ',')")
                    ]);
            }
        }
    }

    public function saving(Model $model)
    {
        $validator = Validator::make($model->toArray(), [
            'parent' => 'integer',
            'name' => 'required'
        ]);

        if ($validator->fails()) {
            throw new \RuntimeException('Validate Fails');
        }
    }

    public function deleted(Model $model)
    {
        $oldNode = $model->getOriginal('node');
        DB::table($model->getTable())
            ->where(DB::raw("LOCATE('{$oldNode}', `node`)"), '>', 0)
            ->delete();
    }

}