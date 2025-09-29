<?php

namespace App\Models\tenant;

use Illuminate\Database\Eloquent\Model;

class GraceDaysRequest extends Model
{

    protected $table = 'grace_days_requests';

    protected $fillable = ['tenant_id', 'days'];
}
