<?php

namespace App\Models\tenant\Traits;

use App\Models\customer\Customer;
use App\Models\tenant_package\TenantPackage;

trait TenantRelationship
{
    public function package()
    {
        return $this->hasOne(TenantPackage::class, 'company_id');
    }

    public function customer()
    {
        return $this->hasOneThrough(Customer::class, TenantPackage::class, 'company_id', 'id', 'id', 'customer_id')
            ->withoutGlobalScope('ins');
    }
}
