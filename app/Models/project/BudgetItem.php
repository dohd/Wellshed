<?php

namespace App\Models\project;

use App\Models\project\Traits\BudgetItemRelationship;
use Illuminate\Database\Eloquent\Model;

class BudgetItem extends Model
{
    use BudgetItemRelationship;

    /**
     * Guarded fields of model
     * @var array
     */
    protected $guarded = [
        'id'
    ];

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
     * Custom scope
     */
    public function scopeOrderByRow($query)
    {
        return $query->orderBy('row_index', 'asc');
    }
}
