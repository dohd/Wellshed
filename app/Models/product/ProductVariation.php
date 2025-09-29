<?php

namespace App\Models\product;

use Illuminate\Database\Eloquent\Model;
use App\Models\product\Traits\ProductVariationRelationship;

class ProductVariation extends Model
{
    use ProductVariationRelationship;
    
    protected $table = 'product_variations';

    /**
     * Mass Assignable fields of model
     * @var array
     */
    protected $fillable = [
        'ins', 'parent_id','productcategory_id', 'name', 'warehouse_id', 
        'code', 'price', 'purchase_price', 'disrate', 'qty',
        'alert', 'image','image_description', 'barcode', 'expiry',
        'moq','fifo_cost',  'asset_account_id', 'exp_account_id',
        'slug_id'
    ];

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
            if (auth()->id()) {
                $instance->ins = auth()->user()->ins;                
            }
            return $instance;
        });

        static::addGlobalScope('ins', function ($builder) {
            if (isset(auth()->user()->ins)) {
                $builder->where('ins', auth()->user()->ins);
            }
        });
    }
}
