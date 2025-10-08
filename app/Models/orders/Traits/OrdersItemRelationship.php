<?php

namespace App\Models\orders\Traits;

use App\Models\orders\Orders;
use App\Models\product\ProductVariation;

/**
 * Class OrdersItemRelationship
 */
trait OrdersItemRelationship
{
    public function orders()
    {
        return $this->belongsTo(Orders::class, 'order_id');
    }

    public function product()
    {
        return $this->belongsTo(ProductVariation::class, 'product_id');
    }
}
