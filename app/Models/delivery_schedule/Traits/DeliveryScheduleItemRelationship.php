<?php

namespace App\Models\delivery_schedule\Traits;

use App\Models\items\OrderItem;
use App\Models\orders\OrdersItem;
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
    public function order_item()
    {
        return $this->belongsTo(OrdersItem::class,'order_item_id');
    }
}
