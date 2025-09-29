<?php

namespace App\Models\warehouse\Traits;

use App\Models\product\ProductVariation;

/**
 * Class WarehouseRelationship
 */
trait WarehouseRelationship
{
    public function products()
    {
        return $this->hasMany(ProductVariation::class);
    }
}
