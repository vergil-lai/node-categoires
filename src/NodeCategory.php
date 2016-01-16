<?php

namespace VergilLai\NodeCategories;

use Illuminate\Database\Eloquent\Model;

class NodeCategory extends Model
{
    /**
     * Get all children node
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function children()
    {
        return $this->where('node', 'like', $this->node.'%')->get();
    }
}
