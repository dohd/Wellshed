<?php

namespace App\Models\subscription\Traits;

use App\Models\customer\Customer;
use App\Models\subpackage\SubPackage;

trait SubscriptionRelationship
{
    public function customer() 
    {
        return $this->belongsTo(Customer::class);
    }

    public function package() 
    {
        return $this->belongsTo(SubPackage::class, 'sub_package_id');
    }
}
