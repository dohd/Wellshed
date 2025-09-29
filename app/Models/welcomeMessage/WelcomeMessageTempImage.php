<?php

namespace App\Models\welcomeMessage;

use Illuminate\Database\Eloquent\Model;

class WelcomeMessageTempImage extends Model
{

    protected $fillable = [
        'location',
        'filename',
    ];


    protected static function boot()
    {
        parent::boot();

        static::creating(function ($instance) {
            $instance->ins = auth()->user()->ins;
            return $instance;
        });

        static::addGlobalScope('ins', function ($builder) {
            if (isset(auth()->user()->ins)) {
                $builder->where('ins', auth()->user()->ins);
            }
        });
    }
}
