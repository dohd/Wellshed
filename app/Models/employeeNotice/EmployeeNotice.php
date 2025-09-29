<?php

namespace App\Models\employeeNotice;

use Illuminate\Database\Eloquent\Model;

class EmployeeNotice extends Model
{

    protected $fillable = [
        'employee_id',
        'title',
        'date',
        'document_path',
        'content'
    ];

    protected static function boot()
    {

        static::creating(function ($instance) {

            $instance->ins = auth()->user()->ins;
            return $instance;
        });

        parent::boot();
        static::addGlobalScope('ins', function ($builder) {
            $builder->where('employee_notices.ins', '=', auth()->user()->ins);
        });
    }

}
