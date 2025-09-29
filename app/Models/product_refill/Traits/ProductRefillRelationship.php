<?php

namespace App\Models\product_refill\Traits;

use App\Models\product_refill\ProductRefillItem;
use App\Models\refill_customer\RefillCustomer;
use App\Models\refill_product\RefillProduct;

trait ProductRefillRelationship
{
    function product_customer()
    {
        return $this->belongsTo(RefillCustomer::class, 'refill_customer_id');
    }

    function refill_products()
    {
        return $this->hasManyThrough(RefillProduct::class, ProductRefillItem::class, 'product_refill_id', 'id', 'id', 'product_id');
    }

    function items()
    {
        return $this->hasMany(ProductRefillItem::class, 'product_refill_id');
    }
}
