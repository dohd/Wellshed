<?php

namespace App\Models\sale_agent\Traits;

use App\Models\sale_agent\SaleAgentProfile;

/**
 * Class SaleAgentRelationship
 */
trait SaleAgentRelationship
{
    public function profile()
    {
    return $this->hasOne(SaleAgentProfile::class);
    }
}
