<?php

namespace App\Models\goodsreceivenote;

use App\Models\goodsreceivenote\Traits\GrnAttribute;
use App\Models\goodsreceivenote\Traits\GrnRelationship;
use App\Models\ModelTrait;
use Illuminate\Database\Eloquent\Model;


class Goodsreceivenote extends Model
{
    use ModelTrait, GrnAttribute, GrnRelationship;

    /**
     * The database table used by the model.
     * @var string
     */
    protected $table = 'goods_receive_notes';

    /**
     * Mass Assignable fields of model
     * @var array
     */
    protected $fillable = [
        'tid', 'supplier_id', 'purchaseorder_id','tax_rate', 'subtotal', 'tax', 'total', 
        'currency_id','fx_curr_rate', 'fx_curr_rate', 'fx_subtotal', 'fx_tax', 'fx_total',
        'date', 'note', 'dnote', 'invoice_no', 'invoice_date', 'user_id', 'ins','jobcard_no'
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
            $instance->user_id = auth()->user()->id;
            $instance->ins = auth()->user()->ins;
            return $instance;
        });

        static::addGlobalScope('ins', function ($builder) {
            $builder->where('ins', '=', auth()->user()->ins);
        });
    }
}
