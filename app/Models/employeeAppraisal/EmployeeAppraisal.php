<?php

namespace App\Models\employeeAppraisal;

use App\Models\attendance\Attendance;
use App\Models\employeeDailyLog\EmployeeDailyLog;
use App\Models\hrm\Hrm;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeAppraisal extends Model
{


    protected $fillable = [
        'employee_id',
        'supervisor_id',
        'appraisal_type_id',
        'start_date',
        'end_date',
        'job_knowledge',
        'quality_of_work',
        'communication',
        'attendance',
        'supervisor_comments',
    ];

    public function employee() : BelongsTo
    {
        return $this->belongsTo(Hrm::class, 'employee_id', 'id');
    }

    public function supervisor() : BelongsTo
    {
        return $this->belongsTo(Hrm::class, 'supervisor_id', 'id');
    }

    public function employee_daily_logs()
    {
        return $this->hasMany(EmployeeDailyLog::class, 'employee','employee_id');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'employee_id', 'employee_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($instance) {

            $instance->ins = auth()->user()->ins;
            return $instance;
        });

        static::addGlobalScope('ins', function ($builder) {
            $builder->where('employee_appraisals.ins', '=', auth()->user()->ins);
        });
    }

}
