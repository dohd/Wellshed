<?php

namespace App\Models\items;

use App\Models\items\Traits\ServiceItemRelationship;
use Illuminate\Database\Eloquent\Model;

class ServiceItem extends Model
{
    use ServiceItemRelationship;
    
    /**
     * The database table used by the model.
     * @var string
     */
    protected $table = 'contract_service_items';

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
}
