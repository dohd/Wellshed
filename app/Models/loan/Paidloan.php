<?php

namespace App\Models\loan;

use App\Models\loan\Traits\PaidloanRelationship;
use Illuminate\Database\Eloquent\Model;

class Paidloan extends Model
{
    use PaidloanRelationship;
    /**
     * The database table used by the model.
     * @var string
     */
    protected $table = 'paid_loans';

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
    }
}
