<?php

namespace App\Models\delivery_schedule\Traits;

use App\Models\product\ProductVariation;

/**
 * Class DeliveryScheduleItemRelationship
 */
trait DeliveryScheduleItemRelationship
{
    public function product()
    {
        return $this->belongsTo(ProductVariation::class, 'product_id');
    }
}
