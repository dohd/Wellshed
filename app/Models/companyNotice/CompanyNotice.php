<?php

namespace App\Models\companyNotice;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class companyNotice extends Model
{

    protected $fillable = ['message'];


    public function images(): HasMany
    {

        return $this->hasMany(CompanyNoticeImage::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($instance) {

            $instance->ins = auth()->user()->ins;
            return $instance;
        });

        static::addGlobalScope('ins', function ($builder) {
            $builder->where('company_notices.ins', '=', auth()->user()->ins);
        });
    }

}
