<?php

namespace App\Models\client_vendor\Traits;

use App\Models\Access\User\User;
use App\Models\customer\Customer;

trait ClientVendorRelationship
{
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->hasOne(User::class, 'client_vendor_id');
    }
}
