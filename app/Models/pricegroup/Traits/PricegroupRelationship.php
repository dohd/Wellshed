<?php

namespace App\Models\pricegroup\Traits;

use App\Models\pricelist\PriceList;

/**
 * Class WarehouseRelationship
 */
trait PricegroupRelationship
{
    public function pricelist()
    {
        return $this->hasMany(PriceList::class, 'pricegroup_id');
    }
}
