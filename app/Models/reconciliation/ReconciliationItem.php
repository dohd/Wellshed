<?php

namespace App\Models\reconciliation;

use App\Models\ModelTrait;
use App\Models\reconciliation\Traits\ReconciliationItemRelationship;
use Illuminate\Database\Eloquent\Model;

class ReconciliationItem extends Model
{
    use ModelTrait, ReconciliationItemRelationship;

    /**
     * The database table used by the model.
     * @var string
     */
    protected $table = 'reconciliation_items';

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
    }
}
