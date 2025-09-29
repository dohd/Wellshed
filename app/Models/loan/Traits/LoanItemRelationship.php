<?php

namespace App\Models\loan\Traits;

use App\Models\Access\User\User;

trait LoanItemRelationship
{
    public function loan()
    {
        return $this->belongsTo(Loan::class, 'loan_id');
    }
}
