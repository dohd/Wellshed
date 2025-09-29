<?php

namespace App\Models\package;

use Illuminate\Database\Eloquent\Model;

class TenantServicePackage extends Model
{

    protected $table = 'tenant_service_packages';

    protected $primaryKey = 'tsp_number';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'package_number',
        'tenant_service_id'
    ];

}
