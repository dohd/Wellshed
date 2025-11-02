<?php

namespace App\Models\payment_receipt;

use App\Models\ModelTrait;
use Illuminate\Database\Eloquent\Model;
use App\Models\payment_receipt\Traits\BranchAttribute;
use App\Models\payment_receipt\Traits\BranchRelationship;

class PaymentReceipt extends Model
{
    use ModelTrait, BranchAttribute, BranchRelationship;

    /**
     * The database table used by the model.
     * @var string
     */
    protected $table = 'payment_receipts';

    /**
     * Mass Assignable fields of model
     * @var array
     */
    protected $fillable = [];

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

        static::creating(function ($model) {
            $model->tid = PaymentReceipt::max('tid')+1;
            if (auth()->id()) {
                $model->created_by = auth()->id();
                $model->ins = auth()->user()->ins;
            }
            return $model;
        });

        static::addGlobalScope('deleted_at', function ($builder) {
            $builder->whereNull('deleted_at');
        });
    }
}
