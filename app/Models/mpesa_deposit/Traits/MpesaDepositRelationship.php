<?php

namespace App\Models\mpesa_deposit\Traits;

use App\Models\tenant\Tenant;

trait MpesaDepositRelationship
{
     public function tenant()
     {
          return $this->belongsTo(Tenant::class, 'owner_id');
     }
}
