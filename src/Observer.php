<?php

namespace VergilLai\NodeCategories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

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

    public function creating(NodeCategory $model)
    {
    }

    public function created(NodeCategory $model)
    {
        $model->parent = isset($model->parent) ? $model->parent : 0;
        $model->syncOriginal();

        if (0 < $model->parent) {
            /**
             * @var NodeCategory $parent
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

    public function updating(NodeCategory $model)
    {
        if ($model->isDirty('parent') && 0 < $model->parent) {
            /**
             * @var NodeCategory $parent
             */
            $parent = $model->findOrFail($model->parent);

            if (empty($parent))
                throw new \RuntimeException('父節點不存在');

            //檢查新的父類是否自己的子類
            if (false !== strpos($parent->node, $model->node))
                throw new \RuntimeException('父類不能是自己的子類');

            $model->level = $parent->level + 1;
        }
    }

    public function updated(NodeCategory $model)
    {

        if ($model->isDirty('parent')) {
            /**
             * 同步Original数据，是為了清除parent的dirty狀態
             * 避免陷入循環，这里很重要
             */
            $model->syncOriginal();

            $tableName = $model->getTable();
            $oldNode = $model->getOriginal('node');

            if (0 < $model->parent) {
                /**
                 * @var NodeCategory $parent
                 */
                $parent = $this->find($model->parent);

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

    public function saving(NodeCategory $model)
    {

        $validator = Validator::make($model->toArray(), [
            'parent' => 'integer',
            'name' => 'required'
        ]);

        if ($validator->fails()) {
            return false;
        }
    }

    public function saved(NodeCategory $model)
    {
    }

    public function deleting(NodeCategory $model)
    {
    }

    public function deleted(NodeCategory $model)
    {
        $oldNode = $model->getOriginal('node');
        DB::table($model->getTable())
            ->where(DB::raw("LOCATE('{$oldNode}', `node`)"), '>', 0)
            ->delete();
    }

}