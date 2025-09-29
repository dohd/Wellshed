<?php

namespace App\Models\tenant_service\Traits;

use App\Models\package\Package;
use App\Models\tenant_service\TenantServiceItem;

trait TenantServiceRelationship
{
    public function items() 
    {
        return $this->hasMany(TenantServiceItem::class);
    }

    public function package()
    {
        return $this->belongsToMany(Package::class, 'tenant_service_packages', 'tenant_service_id', 'package_number')
            ->withPivot('package_number')
            ->withTimestamps();
    }

}
