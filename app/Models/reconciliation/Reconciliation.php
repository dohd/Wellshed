<?php

namespace App\Models\reconciliation;

use App\Models\ModelTrait;
use App\Models\reconciliation\Traits\ReconciliationAtrribute;
use App\Models\reconciliation\Traits\ReconciliationRelationship;
use Illuminate\Database\Eloquent\Model;

class Reconciliation extends Model
{
    use ModelTrait, ReconciliationAtrribute, ReconciliationRelationship;

    /**
     * The database table used by the model.
     * @var string
     */
    protected $table = 'reconciliations';

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

        static::creating(function ($instance) {
            $instance->fill([
                'tid' => Reconciliation::max('tid')+1,
                'user_id' => auth()->user()->id,
                'ins' => auth()->user()->ins,
            ]);
            return $instance;
        });

        static::addGlobalScope('ins', function ($builder) {
            if (isset(auth()->user()->ins)) {
                $builder->where('ins', auth()->user()->ins);
            }
        });
    }
}
