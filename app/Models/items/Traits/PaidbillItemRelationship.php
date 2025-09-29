<?php

namespace App\Models\items\Traits;

use App\Models\bill\Bill;

trait PaidbillItemRelationship
{
    public function bill()
    {
        return $this->belongsTo(Bill::class);
    }
}
