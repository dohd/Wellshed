<?php

namespace App\Models\tenant;

use Illuminate\Database\Eloquent\Model;

class TenantDeactivation extends Model
{

    protected $table = 'tenant_deactivations';

    protected $fillable = ['tenant_id'];
}
