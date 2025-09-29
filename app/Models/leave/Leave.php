<?php

namespace App\Models\leave;

use App\Models\leave\Traits\LeaveAttribute;
use App\Models\leave\Traits\LeaveRelationship;
use App\Models\ModelTrait;
use Illuminate\Database\Eloquent\Model;

class Leave extends Model
{
    use ModelTrait, LeaveAttribute, LeaveRelationship;

    /**
     * NOTE : If you want to implement Soft Deletes in this model,
     * then follow the steps here : https://laravel.com/docs/5.4/eloquent#soft-deleting
     */

    /**
     * The database table used by the model.
     * @var string
     */
    protected $table = 'leaves';

    /**
     * Mass Assignable fields of model
     * @var array
     */
    protected $fillable = [
        'status', 'status_note', 'employee_id',  'assist_employee_id', 'leave_category_id', 'viable_qty', 
        'start_date', 'qty', 'reason', 'end_date', 'return_date', 'handover_note','approver_ids'
    ];

    /**
     * Default values for model fields
     * @var array
     */
    protected $attributes = [];

    /**
     * Dates
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at'
    ];

    /**
     * Guarded fields of model
     * @var array
     */
    protected $guarded = [
        'id'
    ];

    /**
     * Constructor of Model
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    /**
     * model life cycle event listeners
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($instance) {
            $instance->user_id = auth()->user()->id;
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
