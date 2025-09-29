<?php

namespace App\Models\employeeDailyLog;

use App\Models\key_activity\KeyActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeTaskSubcategories extends Model
{

    use SoftDeletes;

    protected $table = 'employee_task_subcategories';

    protected $fillable = [
        'id',
        'name',
        'department',
        'frequency',
        // 'key_activities',
        'key_activity_id',
        'target','uom',
        'ins'
    ];

    public function employeeTasks(): HasMany {

        return $this->hasMany(EmployeeTasks::class, 'subcategory', 'id');
    }

    public function key_activity()
    {
        return $this->belongsTo(KeyActivity::class, 'key_activity_id');
    }


    protected static function boot()
    {
        parent::boot();

        static::creating(function ($instance) {

            $instance->ins = auth()->user()->ins;
            return $instance;
        });

        static::addGlobalScope('ins', function ($builder) {
            $builder->where('employee_task_subcategories.ins', '=', auth()->user()->ins);
        });
    }


}
