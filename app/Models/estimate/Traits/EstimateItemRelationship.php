<?php

namespace App\Models\estimate\Traits;

use App\Models\items\VerifiedItem;
use App\Models\product\ProductVariation;

trait EstimateItemRelationship
{    
    public function productvar()
    {
        return $this->belongsTo(ProductVariation::class, 'productvar_id');
    }

    public function vrf_item()
    {
        return $this->belongsTo(VerifiedItem::class, 'vrf_item_id');
    }
}
