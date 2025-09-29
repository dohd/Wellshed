<?php

namespace App\Models\client_user\Traits;

use App\Models\Access\User\User;
use App\Models\branch\Branch;
use App\Models\customer\Customer;

trait ClientUserRelationship
{
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function user()
    {
        return $this->hasOne(User::class, 'client_user_id');
    }
}
