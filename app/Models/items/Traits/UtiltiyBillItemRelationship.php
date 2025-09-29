<?php

namespace App\Models\items\Traits;

use App\Models\utility_bill\UtilityBill;

trait UtiltiyBillItemRelationship
{
    public function bill()
    {
        return $this->belongsTo(UtilityBill::class);
    }
}
