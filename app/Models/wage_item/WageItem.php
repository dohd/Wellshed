<?php

namespace App\Models\wage_item;

use App\Models\ModelTrait;
use App\Models\wage_item\Traits\WageItemAttribute;
use App\Models\wage_item\Traits\WageItemRelationship;
use Illuminate\Database\Eloquent\Model;

class WageItem extends Model
{
    use ModelTrait, WageItemAttribute, WageItemRelationship;

    /**
     * The database table used by the model.
     * @var string
     */
    protected $table = 'wage_items';

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
                'user_id' => auth()->user()->id,
                'ins' => auth()->user()->ins,
            ]);
            return $model;
        });

        static::addGlobalScope('ins', function ($builder) {
            $builder->where('ins', auth()->user()->ins);
        });
    }
}
