<?php

namespace App\Models\items\Traits;

use App\Models\loan\Loan;

trait PaidloanItemRelationship
{
   public function loan()
   {
       return $this->belongsTo(Loan::class, 'loan_id');
   }
}
