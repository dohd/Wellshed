<?php

namespace App\Models\classlist;

use App\Models\ModelTrait;
use Illuminate\Database\Eloquent\Model;
use App\Models\classlist\Traits\ClasslistAttribute;
use App\Models\classlist\Traits\ClasslistRelationship;

class Classlist extends Model
{
    use ModelTrait, ClasslistAttribute, ClasslistRelationship;

    /**
     * The database table used by the model.
     * @var string
     */
    protected $table = 'classlists';

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
            $model->fill([
                'tid' => Classlist::max('tid')+1,
                'user_id' => auth()->user()->id,
                'ins' => auth()->user()->ins,
            ]);
            return $model;
        });
        
        static::addGlobalScope('ins', function ($builder) {
            if (isset(auth()->user()->ins)) {
                $builder->where('ins', auth()->user()->ins);
            }
        });
    }
}
