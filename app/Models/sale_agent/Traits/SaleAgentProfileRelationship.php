<?php

namespace App\Models\sale_agent\Traits;

use App\Models\sale_agent\SaleAgent;
use App\Models\sale_agent\SaleAgentProfile;

/**
 * Class SaleAgentProfileRelationship
 */
trait SaleAgentProfileRelationship
{
    public function agent()
    {
        return $this->belongsTo(SaleAgent::class);
    }
}
