<?php

namespace App\Models\verifiedjcs\Traits;

use App\Models\equipment\Equipment;

/**
 * Class WithholdingRelationship
 */
trait VerifiedJcRelationship
{
    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }
}
