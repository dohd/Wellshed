<?php

namespace App\Models\dailyBusinessMetric;

use Illuminate\Database\Eloquent\Model;

class DbmDisplayOption extends Model
{

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($instance) {

            $instance->ins = auth()->user()->ins;
            return $instance;
        });
    }

}
