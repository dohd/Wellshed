<?php

namespace App\Models\payment_receipt\Traits;

use App\Models\customer\Customer;

trait BranchRelationship
{
    public function customer()
    {
    	return $this->belongsTo(Customer::class);
    }
}