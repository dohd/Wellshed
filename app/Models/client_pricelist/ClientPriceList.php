<?php

namespace App\Models\pricelist;

use App\Models\client_pricelist\Traits\ClientPriceListAttribute;
use App\Models\client_pricelist\Traits\ClientPriceListRelationship;
use App\Models\ModelTrait;
use Illuminate\Database\Eloquent\Model;

class ClientPriceList extends Model
{
    use ModelTrait, ClientPriceListAttribute, ClientPriceListRelationship;

    protected $table = 'client_pricelists';

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
