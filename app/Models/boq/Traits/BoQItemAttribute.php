<?php

namespace App\Models\boq\Traits;

trait BoQItemAttribute
{
    public function scopeOrderByRow($query) 
    {
        return $query->orderBy('row_index', 'asc');
    }
}