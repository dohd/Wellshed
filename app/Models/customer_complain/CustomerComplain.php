<?php

namespace App\Models\customer_complain;

use App\Models\employeeDailyLog\EmployeeTaskSubcategories;
use App\Models\ModelTrait;
use Illuminate\Database\Eloquent\Model;
use App\Models\customer_complain\Traits\CustomerComplainAttribute;
use App\Models\customer_complain\Traits\CustomerComplainRelationship;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class CustomerComplain extends Model
{
    use ModelTrait,
        CustomerComplainAttribute,
        CustomerComplainRelationship {
        // CustomerComplainAttribute::getEditButtonAttribute insteadof ModelTrait;
    }

    /**
     * NOTE : If you want to implement Soft Deletes in this model,
     * then follow the steps here : https://laravel.com/docs/5.4/eloquent#soft-deleting
     */

    /**
     * The database table used by the model.
     * @var string
     */
    protected $table = 'customer_complains';

    /**
     * Mass Assignable fields of model
     * @var array
     */
    protected $fillable = ['customer_id','project_id','employees','solver_id','issue_description','initial_scale','current_scale','customer_feedback_id',
                            'type_of_complaint','date','planing','doing','checking','customer_feedback','customer_feedback','comments', 'ins'];

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
    
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($instance) {

            if (Auth::user()) {

                $instance->user_id = auth()->user()->id;
                $instance->ins = auth()->user()->ins;
            }
            return $instance;
        });

        static::addGlobalScope('ins', function ($builder) {
            if (isset(auth()->user()->ins)) {
                $builder->where('ins', auth()->user()->ins);
            }
        });
    }

}
