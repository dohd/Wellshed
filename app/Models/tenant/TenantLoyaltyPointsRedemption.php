<?php

namespace App\Models\tenant;

use Illuminate\Database\Eloquent\Model;

class TenantLoyaltyPointsRedemption extends Model
{

    protected $table = 'tenant_loyalty_points_redemptions';

    protected $fillable = ['tenant_id','points','days'];
}
