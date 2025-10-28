<?php

namespace App\Models\subpackage;

use App\Models\ModelTrait;
use Illuminate\Database\Eloquent\Model;
use App\Models\subpackage\Traits\BranchAttribute;
use App\Models\subpackage\Traits\BranchRelationship;

class SubPackage extends Model
{
    use ModelTrait,
        BranchAttribute,
        BranchRelationship;

    /**
     * The database table used by the model.
     * @var string
     */
    protected $table = 'sub_packages';

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

        if (auth()->id()) {
            static::creating(function ($model) {
                $model->tid = SubPackage::max('tid')+1;
                $model->created_by = auth()->user()->id;
                $model->ins = auth()->user()->ins;
                return $model;
            });
            static::addGlobalScope('ins', function ($builder) {
                $builder->where('ins', auth()->user()->ins);
            });
        }
    }
}
