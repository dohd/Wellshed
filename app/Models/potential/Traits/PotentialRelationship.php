<?php

namespace App\Models\potential\Traits;

use App\Models\lead\Lead;

/**
 * Class PotentialRelationship
 */
trait PotentialRelationship
{
     public function lead()
     {
        return $this->belongsTo(Lead::class, 'lead_id');
     }
}
