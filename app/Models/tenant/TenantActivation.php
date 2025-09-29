<?php

namespace App\Models\tenant;

use Illuminate\Database\Eloquent\Model;

class TenantActivation extends Model
{

    protected $table = 'tenant_activations';

    protected $fillable = ['tenant_id'];

}
