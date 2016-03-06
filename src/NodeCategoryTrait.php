<?php

namespace VergilLai\NodeCategories;

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
}
