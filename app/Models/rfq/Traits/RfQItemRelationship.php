<?php

namespace App\Models\rfq\Traits;

use App\Models\assetequipment\Assetequipment;

trait RfQItemRelationship
{
    public function asset()
    {
        return $this->belongsTo(Assetequipment::class, 'item_id');
    }
}
