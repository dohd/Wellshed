<?php

namespace App\Models\employeeDailyLog;

use App\Models\Access\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeDailyLog extends Model
{

    use SoftDeletes;

    protected $table = 'employee_daily_logs';

    protected $primaryKey = 'edl_number';

    public $incrementing = false;

    protected $keyType = 'string';


    protected $fillable = [
        'date',
        'rating',
        'remarks',
        'reviewer',
        'reviewed_at',
        'ins'
    ];


    public function tasks(): HasMany {

        return $this->hasMany(EmployeeTasks::class, 'edl_number', 'edl_number');
    }

    public function user() {
        return $this->belongsTo(User::class, 'employee', 'id');
    }


    protected static function boot()
    {
        parent::boot();

        static::creating(function ($instance) {
            $instance->ins = auth()->user()->ins;
            $instance->rating = 4;
            return $instance;
        });

        static::addGlobalScope('ins', function ($builder) {
            $builder->where('employee_daily_logs.ins', '=', auth()->user()->ins);
        });
    }


}
