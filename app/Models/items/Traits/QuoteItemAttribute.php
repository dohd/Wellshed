<?php

namespace App\Models\items\Traits;

trait QuoteItemAttribute
{
    public function scopeOrderByRow($query) 
    {
        return $query->orderBy('row_index', 'asc');
    }
}