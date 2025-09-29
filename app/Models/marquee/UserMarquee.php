<?php

namespace App\Models\marquee;

use Illuminate\Database\Eloquent\Model;

class UserMarquee extends Model
{

    protected $table = 'user_marquees';

    protected $fillable = ['content', 'start', 'end'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($instance) {

            $instance->ins = auth()->user()->ins;
            return $instance;
        });

        static::updating(function ($instance) {

            $instance->ins = auth()->user()->ins;
            return $instance;
        });


        static::addGlobalScope('ins', function ($builder) {
            $builder->where('user_marquees.ins', '=', auth()->user()->ins);
        });
    }

}
