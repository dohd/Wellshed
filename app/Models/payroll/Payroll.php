<?php

namespace App\Models\payroll;

use App\Models\ModelTrait;
use Illuminate\Database\Eloquent\Model;
use App\Models\payroll\Traits\PayrollAttribute;
use App\Models\payroll\Traits\PayrollRelationship;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payroll extends Model
{
    use ModelTrait,
        SoftDeletes,
        PayrollAttribute,
    	PayrollRelationship {
            // payrollAttribute::getEditButtonAttribute insteadof ModelTrait;
        }

    /**
     * NOTE : If you want to implement Soft Deletes in this model,
     * then follow the steps here : https://laravel.com/docs/5.4/eloquent#soft-deleting
     */

    /**
     * The database table used by the model.
     * @var string
     */
    protected $table = 'payroll';

    /**
     * Mass Assignable fields of model
     * @var array
     */
    protected $fillable = [

    ];

    /**
     * Default values for model fields
     * @var array
     */
    protected $attributes = [

    ];

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
                 $instance->user_id = auth()->user()->id;
                // $instance->ins = auth()->user()->ins;
                $instance->tid = Payroll::max('tid') + 1;
                return $instance;
            });
                static::addGlobalScope('ins', function($builder){
            $builder->where('payroll.ins', '=', auth()->user()->ins);
    });
    }
}
