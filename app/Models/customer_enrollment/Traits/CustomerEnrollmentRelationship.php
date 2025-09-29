<?php

namespace App\Models\customer_enrollment\Traits;

use App\Models\customer\Customer;
use App\Models\customer_enrollment\CustomerEnrollmentItem;

/**
 * Class CustomerEnrollmentRelationship
 */
trait CustomerEnrollmentRelationship
{
    public function items()
    {
        return $this->hasMany(CustomerEnrollmentItem::class, 'customer_enrollment_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'client_id');
    }
}
