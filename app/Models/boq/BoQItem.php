<?php

namespace App\Models\boq;

use App\Models\ModelTrait;
use Illuminate\Database\Eloquent\Model;
use App\Models\boq\Traits\BoQItemAttribute;
use App\Models\boq\Traits\BoQItemRelationship;

class BoQItem extends Model
{
    use ModelTrait,
    BoQItemAttribute,
    BoQItemRelationship {
        // boqAttribute::getEditButtonAttribute insteadof ModelTrait;
    }

    /**
     * NOTE : If you want to implement Soft Deletes in this model,
     * then follow the steps here : https://laravel.com/docs/5.4/eloquent#soft-deleting
     */

    /**
     * The database table used by the model.
     * @var string
     */
    protected $table = 'boq_items';

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
            $instance->user_id = auth()->user()->id;
            $instance->ins = auth()->user()->ins;
            return $instance;
        });

        // static::addGlobalScope('ins', function ($builder) {
        //     if (isset(auth()->user()->ins)) {
        //         $builder->where('ins', auth()->user()->ins);
        //     }
        // });
    }
}
