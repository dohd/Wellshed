<?php

namespace App\Models\items;

use App\Models\items\Traits\OpeningStockItemRelationship;
use Illuminate\Database\Eloquent\Model;


class OpeningStockItem extends Model
{
    use OpeningStockItemRelationship;
    
    /**
     * NOTE : If you want to implement Soft Deletes in this model,
     * then follow the steps here : https://laravel.com/docs/5.4/eloquent#soft-deleting
     */

    /**
     * The database table used by the model.
     * @var string
     */
    protected $table = 'opening_stock_items';

    /**
     * Mass Assignable fields of model
     * @var array
     */
    protected $fillable = [
        'opening_stock_id', 'parent_id', 'product_id', 'qty_alert', 'purchase_price', 'qty', 
        'amount'
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
     * 
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }
}
