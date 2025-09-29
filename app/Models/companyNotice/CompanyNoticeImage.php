<?php

namespace App\Models\companyNotice;

use Illuminate\Database\Eloquent\Model;

class CompanyNoticeImage extends Model
{

    protected $fillable = [
        'company_notice_id',
        'name',
        'description',
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
