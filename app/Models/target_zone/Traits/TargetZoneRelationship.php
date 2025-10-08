<?php

namespace App\Models\target_zone\Traits;

use App\Models\target_zone\TargetZoneItem;

/**
 * Class TargetZoneRelationship
 */
trait TargetZoneRelationship
{
    public function items()
    {
        return $this->hasMany(TargetZoneItem::class,'target_zone_id');
    }
}
