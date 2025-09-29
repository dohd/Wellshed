<?php

namespace App\Models\documentBoard;

use App\Models\welcomeMessage\WelcomeMessageImage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WelcomeMessage extends Model
{

    protected $fillable = ['message'];


    public function images(): HasMany {

        return $this->hasMany(WelcomeMessageImage::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($instance) {

            $instance->ins = auth()->user()->ins;
            return $instance;
        });

        static::addGlobalScope('ins', function ($builder) {
            $builder->where('welcome_messages.ins', '=', auth()->user()->ins);
        });
    }
}
