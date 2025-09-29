<?php

namespace App\Models\product;

use App\Models\ModelTrait;
use Illuminate\Database\Eloquent\Model;
use App\Models\product\Traits\ProductAttribute;
use App\Models\product\Traits\ProductRelationship;

class Product extends Model
{
    use ModelTrait, ProductAttribute, ProductRelationship;
        
    /**
     * NOTE : If you want to implement Soft Deletes in this model,
     * then follow the steps here : https://laravel.com/docs/5.4/eloquent#soft-deleting
     */

    /**
     * The database table used by the model.
     * @var string
     */
    protected $table = 'products';

    /**
     * Mass Assignable fields of model
     * @var array
     */
    protected $fillable = [
        'ins', 'user_id', 'productcategory_id', 'name', 'taxrate', 'product_des', 'unit_id', 'code_type', 'sku',
        'sub_cat_id', 'brand_id', 'stock_type', 'taxrate', 'slug_id',
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

    // Append the custom attribute to the model's array form
    // protected $appends = ['action_buttons'];

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
                $instance->user_id = auth()->user()->id;
                $instance->ins = auth()->user()->ins;                
            }
            return $instance;
        });

        static::addGlobalScope('ins', function ($builder) {
            if (auth()->id()) {
                $builder->where('ins', '=', auth()->user()->ins);
            }
        });
    }
}
