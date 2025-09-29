<?php

namespace App\Models\client_pricelist\Traits;

use App\Models\customer\Customer;
use App\Models\supplier\Supplier;

trait ClientPriceListRelationship
{
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'ref_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'ref_id');
    }
}
