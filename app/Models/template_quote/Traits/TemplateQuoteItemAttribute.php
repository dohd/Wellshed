<?php

namespace App\Models\template_quote\Traits;

trait TemplateQuoteItemAttribute
{
    public function scopeOrderByRow($query) 
    {
        return $query->orderBy('row_index', 'asc');
    }
}