<?php

namespace App\Models\items;

use App\Models\items\Traits\GrnItemRelationship;
use Illuminate\Database\Eloquent\Model;


class GoodsreceivenoteItem extends Model
{
    use GrnItemRelationship;

    /**
     * The database table used by the model.
     * @var string
     */
    protected $table = 'goods_receive_note_items';

    /**
     * Mass Assignable fields of model
     * @var array
     */
    protected $fillable = [
        'goods_receive_note_id', 'purchaseorder_item_id', 'item_id', 'qty', 'rate', 'tax_rate', 
        'fx_rate', 'fx_subtotal', 'fx_taxable', 'fx_tax', 'fx_amount'
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
    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('ins', function ($builder) {
            // $builder->where('ins', '=', auth()->user()->ins);
        });
    }
}
