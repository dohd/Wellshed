<?php

namespace App\Models\items;

use App\Models\items\Traits\ProjectstockItemRelationship;
use Illuminate\Database\Eloquent\Model;

class ProjectstockItem extends Model
{
    use ProjectstockItemRelationship;

    protected $table = 'project_stock_items';

    /**
     * Mass Assignable fields of model
     * @var array
     */
    protected $fillable = [
        'budget_item_id', 'product_id', 'unit', 'warehouse_id', 'unit', 'qty'
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
}
