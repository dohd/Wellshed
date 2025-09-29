<?php

namespace App\Models\sell_price;

use App\Models\import_request\ImportRequestItem;
use App\Models\ModelTrait;
use App\Models\product\ProductVariation;
use Illuminate\Database\Eloquent\Model;

class SellPriceItem extends Model
{

    /**
     * NOTE : If you want to implement Soft Deletes in this model,
     * then follow the steps here : https://laravel.com/docs/5.4/eloquent#soft-deleting
     */

    /**
     * The database table used by the model.
     * @var string
     */
    protected $table = 'sell_price_items';

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

        static::addGlobalScope('ins', function ($builder) {
            if (isset(auth()->user()->ins)) {
                $builder->where('ins', auth()->user()->ins);
            }
        });
    }

    public function sell_price()
    {
        return $this->belongsTo(SellPrice::class, 'sell_price_id');
    }

    public function import_req_item()
    {
        return $this->belongsTo(ImportRequestItem::class, 'import_request_item_id');
    }
    public function product()
    {
        return $this->belongsTo(ProductVariation::class, 'product_id');
    }
}
