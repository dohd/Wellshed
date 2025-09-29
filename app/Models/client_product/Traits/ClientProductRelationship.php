<?php

namespace App\Models\client_product\Traits;

use App\Models\customer\Customer;
use App\Models\product\ProductVariation;

trait ClientProductRelationship
{
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    public function variation(){
        return $this->belongsTo(ProductVariation::class, 'item_id');
    }
}
