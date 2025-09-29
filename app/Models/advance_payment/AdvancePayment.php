<?php

namespace App\Models\advance_payment;

use App\Models\advance_payment\Traits\AdvancePaymentAttribute;
use App\Models\advance_payment\Traits\AdvancePaymentRelationship;
use App\Models\ModelTrait;
use Illuminate\Database\Eloquent\Model;

class AdvancePayment extends Model
{
    use ModelTrait, AdvancePaymentAttribute, AdvancePaymentRelationship;

    /**
     * NOTE : If you want to implement Soft Deletes in this model,
     * then follow the steps here : https://laravel.com/docs/5.4/eloquent#soft-deleting
     */

    /**
     * The database table used by the model.
     * @var string
     */
    protected $table = 'advance_payments';

    /**
     * Mass Assignable fields of model
     * @var array
     */
    protected $fillable = [
       'employee_id', 'amount', 'date', 'status', 'approve_date', 'approve_amount', 'approve_note', 'notes'
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
            $builder->where('ins', '=', auth()->user()->ins);
        });
    }
}
