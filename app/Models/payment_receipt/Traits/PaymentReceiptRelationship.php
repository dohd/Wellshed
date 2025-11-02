<?php

namespace App\Models\payment_receipt\Traits;

use App\Models\customer\Customer;
use App\Models\orders\Orders;
use App\Models\subscription\Subscription;

trait PaymentReceiptRelationship
{
    public function order()
    {
        return $this->belongsTo(Orders::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function customer()
    {
    	return $this->belongsTo(Customer::class);
    }
}