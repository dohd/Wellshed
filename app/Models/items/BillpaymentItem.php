<?php

namespace App\Models\items;

use App\Models\items\Traits\BillpaymentItemRelationship;
use Illuminate\Database\Eloquent\Model;


class BillpaymentItem extends Model
{
    use BillpaymentItemRelationship;
    
    /**
     * NOTE : If you want to implement Soft Deletes in this model,
     * then follow the steps here : https://laravel.com/docs/5.4/eloquent#soft-deleting
     */

    /**
     * The database table used by the model.
     * @var string
     */
    protected $table = 'bill_payment_items';

    /**
     * Mass Assignable fields of model
     * @var array
     */
    protected $fillable = ['bill_payment_id', 'bill_id', 'paid'];

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
        static::addGlobalScope('ins', function ($builder) {
            // $builder->where('ins', '=', auth()->user()->ins);
        });
    }
}
