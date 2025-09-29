<?php

namespace App\Models\tenant_package\Traits;

use App\Models\customer\Customer;
use App\Models\tenant\Tenant;
use App\Models\tenant_package\TenantPackageItem;
use App\Models\tenant_service\TenantService;

trait TenantPackageRelationship
{
    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'company_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(TenantPackageItem::class, 'tenant_package_id');
    }

    public function service()
    {
        return $this->belongsTo(TenantService::class, 'package_id');
    }
}
