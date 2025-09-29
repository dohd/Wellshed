<?php

namespace App\Models\jobGrade;

use Illuminate\Database\Eloquent\Model;

class JobGrade extends Model
{

    protected $fillable = [
        "1a_upper",
        "1a_lower",
        "1b_upper",
        "1b_lower",
        "2a_upper",
        "2a_lower",
        "2b_upper",
        "2b_lower",
        "3a_upper",
        "3a_lower",
        "3b_upper",
        "3b_lower",
        "4a_upper",
        "4a_lower",
        "4b_upper",
        "4b_lower",
        "5a_upper",
        "5a_lower",
        "5b_upper",
        "5b_lower",
        "6a_upper",
        "6a_lower",
        "6b_upper",
        "6b_lower",
        "7a_upper",
        "7a_lower",
        "7b_upper",
        "7b_lower",
        "8a_upper",
        "8a_lower",
        "8b_upper",
        "8b_lower",
        "9a_upper",
        "9a_lower",
        "9b_upper",
        "9b_lower",
        "10a_upper",
        "10a_lower",
        "10b_upper",
        "10b_lower"
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($instance) {

            $instance->ins = auth()->user()->ins;
            return $instance;
        });

        static::addGlobalScope('ins', function ($builder) {
            $builder->where('job_grades.ins', '=', auth()->user()->ins);
        });
    }

}
