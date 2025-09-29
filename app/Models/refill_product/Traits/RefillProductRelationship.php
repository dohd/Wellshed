<?php

namespace App\Models\refill_product\Traits;

use App\Models\refill_product_category\RefillProductCategory;

trait RefillProductRelationship
{
    function product_category()
    {
        return $this->belongsTo(RefillProductCategory::class, 'productcategory_id');
    }
}
