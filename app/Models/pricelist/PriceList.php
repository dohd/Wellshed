<?php

namespace App\Models\pricelist;

use App\Models\ModelTrait;
use App\Models\pricelist\Traits\PriceListAttribute;
use App\Models\pricelist\Traits\PriceListRelationship;
use Illuminate\Database\Eloquent\Model;

class PriceList extends Model
{
    use ModelTrait, PriceListRelationship, PriceListAttribute;

    protected $table = 'price_lists';

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
        static::addGlobalScope('ins', function ($builder) {
            $builder->where('ins', '=', auth()->user()->ins);
        });
    }
}
