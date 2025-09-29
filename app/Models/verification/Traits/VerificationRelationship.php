<?php

namespace App\Models\verification\Traits;

use App\Models\branch\Branch;
use App\Models\customer\Customer;
use App\Models\items\VerificationItem;
use App\Models\quote\Quote;
use App\Models\verification\VerificationJc;

trait VerificationRelationship
{
    public function items()
    {
        return $this->hasMany(VerificationItem::class, 'parent_id');
    }

    public function jc_items()
    {
        return $this->hasMany(VerificationJc::class, 'parent_id');
    }

    public function quote()
    {
        return $this->belongsTo(Quote::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }


}
