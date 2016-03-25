<?php

namespace VergilLai\NodeCategories;

use DB;
use Illuminate\Database\Eloquent\Model;

/**
 * Class NodeCategoryTrait
 *
 * @package VergilLai\NodeCategories
 * @author Vergil <vergil@vip.163.com>
 */
trait NodeCategoryTrait
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id', 'id');
    }

    public function getParent()
    {
        return $this->findOrFail($this->parent_id);
    }

    /**
     * Get all children node
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function childrens()
    {
        return $this->where('node', 'like', $this->node.'%')->get();
    }

    /**
     * Get all parent node
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function parents()
    {
        return $this->where(DB::raw("LOCATE(`node`, '{$this->node}')"), '>', 0)
            ->where('id', '<>', $this->id)->get();
    }
}
