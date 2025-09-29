<?php

namespace App\Models\quality_tracking;

use App\Models\branch\Branch;
use App\Models\customer\Customer;
use App\Models\health_and_safety_objectives\HealthAndSafetyObjective;
use App\Models\hrm\Hrm;
use App\Models\ModelTrait;
use App\Models\project\Project;
use App\Models\promotions\ClientFeedback;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class QualityTracking extends Model
{
    use ModelTrait;

    protected $table = 'quality_tracking';

    protected $fillable = [

        'date',
        'customer_id',
        'branch_id',
        'project_id',
        'employee',
        'incident_desc',
        'route_course',
        'status',
        'pdca_cycle',
        'responsibility',
        'timing',
        'comments',
        'ins',
        'user_id',
        'plan',
        'do',
        'check',
        'act',
        'countermeasure',
        'cm_responsible_person',
        'completion_date',
        'verification',
        'customer_feedback_id',
    ];

    protected $attributes = [];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    protected $guarded = [
        'id'
    ];


    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    public function customer(){
        return $this->belongsTo(Customer::class,  'customer_id');
    }
    public function branch(){
        return $this->belongsTo(Branch::class,  'branch_id');
    }
    public function project(){
        return $this->belongsTo(Project::class, 'project_id');
    }
    public function employee(){
        return $this->belongsTo(Hrm::class, 'employee');
    }
    public function res(){
        return $this->belongsTo(Hrm::class, 'responsibility');
    }

    public function cmResponsible(): BelongsTo{
        return $this->belongsTo(Hrm::class, 'cm_responsible_person', 'id');
    }

    public function client_feedback()
    {
        return $this->belongsTo(ClientFeedback::class, 'customer_feedback_id');
    }


    public function getActionButtonsAttribute()
    {
        return '
         '.$this->getViewButtonAttribute("create-daily-logs", "biller.quality-tracking.show").'
                '.$this->getEditButtonAttribute("create-daily-logs", "biller.quality-tracking.edit").'
                '.$this->getDeleteButtonAttribute("create-daily-logs", "biller.quality-tracking.destroy").'
                ';
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($instance) {

            if (Auth::user()) {

                $instance->user_id = auth()->user()->id;
                $instance->ins = auth()->user()->ins;
                return $instance;
            }
        });

        static::addGlobalScope('ins', function ($builder) {
            $builder->where('ins', '=', auth()->user()->ins);
        });
    }
}
