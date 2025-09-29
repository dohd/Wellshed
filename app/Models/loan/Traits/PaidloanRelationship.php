<?php

namespace App\Models\loan\Traits;

use App\Models\items\PaidloanItem;

trait PaidloanRelationship
{
    public function items()
    {
        return $this->hasMany(PaidloanItem::class, 'paid_loan_id');
    }
}
