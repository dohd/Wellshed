<?php

namespace App\Models\items;

use App\Models\items\Traits\UtiltiyBillItemRelationship;
use Illuminate\Database\Eloquent\Model;

class UtilityBillItem extends Model
{
    use UtiltiyBillItemRelationship;
    
    /**
     * The database table used by the model.
     * @var string
     */
    protected $table = 'utility_bill_items';

    /**
     * Mass Assignable fields of model
     * @var array
     */
    protected $fillable = [
        'bill_id', 'ref_id', 'note', 'qty', 'subtotal', 'tax', 'total', 'fx_subtotal', 'fx_taxable', 'fx_tax', 'fx_total'
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
    }
}
