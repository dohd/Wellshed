<?php

namespace App\Models\labour_allocation;

use App\Models\ModelTrait;
use Illuminate\Database\Eloquent\Model;
use App\Models\labour_allocation\Traits\LabourAllocationAttribute;
use App\Models\labour_allocation\Traits\LabourAllocationRelationship;

class LabourAllocation extends Model
{
    use ModelTrait,
        LabourAllocationAttribute,
        LabourAllocationRelationship {
        // ProductcategoryAttribute::getEditButtonAttribute insteadof ModelTrait;
    }

    /**
     * The database table used by the model.
     * @var string
     */
    protected $table = 'labour_allocations';

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

    /**
     * model life cycle event listeners
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($instance) {
            // $instance->user_id = auth()->user()->id;
            $instance->ins = auth()->user()->ins;
            return $instance;
        });

        static::addGlobalScope('ins', function ($builder) {
            $builder->where('labour_allocations.ins', auth()->user()->ins);
        });
    }
}
