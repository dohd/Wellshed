<?php

namespace App\Models\delivery\Traits;

use App\Models\product\ProductVariation;

/**
 * Class DeliveryItemRelationship
 */
trait DeliveryItemRelationship
{
    public function product()
    {
        return $this->belongsTo(ProductVariation::class, 'product_id');
    }
}
