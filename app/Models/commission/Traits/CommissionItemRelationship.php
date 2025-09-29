<?php

namespace App\Models\commission\Traits;

use App\Models\commission\Commission;

/**
 * Class CommissionItemRelationship
 */
trait CommissionItemRelationship
{
    public function commission()
    {
        return $this->belongsTo(Commission::class, 'commission_id');
    }
}
