<?php

namespace App\Models\dailyBusinessMetric;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DailyBusinessMetric extends Model
{

    protected $primaryKey = 'dbm_uuid';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [];

}
