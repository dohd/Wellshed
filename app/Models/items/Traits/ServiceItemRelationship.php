<?php

namespace App\Models\items\Traits;

use App\Models\contractservice\ContractService;

trait ServiceItemRelationship
{
    public function contract_service()
    {
        return $this->belongsTo(ContractService::class, 'contractservice_id');
    }
}
