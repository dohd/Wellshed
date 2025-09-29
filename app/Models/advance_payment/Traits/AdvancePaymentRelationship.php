<?php

namespace App\Models\advance_payment\Traits;

use App\Models\Access\User\User;
use App\Models\hrm\Hrm;

trait AdvancePaymentRelationship
{
    public function employee()
    {
        return $this->belongsTo(Hrm::class);
    }
}
