<?php

namespace VergilLai\NodeCategories;

use Cache;
use DB;
use Validator;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Observer
 * Node Category 模型观察者
 * @package VergilLai\NodeCategories
 * @author vergil <vergil@vip.163.com>
 */
class Observer
{
    const SEPARATOR = ',';

    public function created(Model $model)
    {
        $model->parent_id = isset($model->parent_id) ? $model->parent_id : 0;
        $model->syncOriginal();

        if (0 < $model->parent_id) {
            /**
             * @var Model $parent
             */
            $parent = $model->findOrFail($model->parent_id);
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
        if ($model->isDirty('parent_id') && 0 < $model->parent_id) {
            /**
             * @var Model $parent
             */
            $parent = $model->findOrFail($model->parent_id);

            if (empty($parent))
                throw new \RuntimeException('Parent node is not exists.');

            //檢查新的父類是否自己的子類
            if (false !== strpos($parent->node, $model->node))
                throw new \RuntimeException('The parent can not be your own subclass');

            //把原来的level先保存在缓存中
            Cache::put('node-category-original-level-'. $model->id, $model->level, 1);

            $model->level = $parent->level + 1;
        }
    }

    public function updated(Model $model)
    {
        if ($model->isDirty('parent_id')) {
            /**
             * 同步Original数据，是為了清除parent_id的dirty狀態
             */
            $model->syncOriginal();

            $tableName = $model->getTable();
            $oldNode = $model->getOriginal('node');

            if (0 < $model->parent_id) {
                /**
                 * @var Model  $parent
                 */
                $parent = $model->find($model->parent_id);

                $model->node = $parent->node . $model->id . self::SEPARATOR;

                $model->save();

                //取出原来的level
                $originalLevel = Cache::pull('node-category-original-level-'. $model->id);
                //计算新level与原来的level的差值
                $i = $model->level - $originalLevel;
                DB::table($tableName)
                    ->where('node', 'like', $oldNode.'%')
                    ->where('id', '<>', $model->id)
                    ->update([
                        'level' => DB::raw("level + {$i}"),
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
            'parent_id' => 'integer',
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