<?php

namespace App\Models\bom\Traits;

trait BoMItemAttribute
{
    public function scopeOrderByRow($query) 
    {
        return $query->orderBy('row_index', 'asc');
    }
}