<?php

namespace App\Models\employeeDailyLog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeTasks extends Model
{

    use SoftDeletes;

    protected $table = 'employee_tasks';

    protected $primaryKey = 'et_number';

    public $incrementing = false;

    protected $keyType = 'string';


    protected $fillable = [
        'edl_number',
        'category',
        'description',
        'hours',
        'performance',
        'work_done','ins'
    ];

    public function employeeDailyLog()
    {
        return $this->belongsTo(EmployeeDailyLog::class, 'edl_number');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($instance) {

            $instance->ins = auth()->user()->ins;
            return $instance;
        });

        static::addGlobalScope('ins', function ($builder) {
            $builder->where('employee_tasks.ins', '=', auth()->user()->ins);
        });
    }


}
