<?php

namespace App\Models\delivery\Traits;

use App\Models\delivery_schedule\DeliveryScheduleItem;
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

    public function delivery_schedule_item()
    {
        return $this->belongsTo(DeliveryScheduleItem::class, 'delivery_schedule_item_id');
    }
}
