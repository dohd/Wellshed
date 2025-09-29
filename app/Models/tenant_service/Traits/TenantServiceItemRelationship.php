<?php

namespace App\Models\tenant_service\Traits;

use App\Models\tenant_service\PackageExtra;

trait TenantServiceItemRelationship
{
    public function package_extra() 
    {
        return $this->belongsTo(PackageExtra::class, 'package_id');
    }
}
