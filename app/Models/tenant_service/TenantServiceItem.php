<?php

namespace App\Models\tenant_service;

use App\Models\tenant_service\Traits\TenantServiceItemRelationship;
use Illuminate\Database\Eloquent\Model;

class TenantServiceItem extends Model
{
    use TenantServiceItemRelationship;
    
    /**
     * NOTE : If you want to implement Soft Deletes in this model,
     * then follow the steps here : https://laravel.com/docs/5.4/eloquent#soft-deleting
     */

    /**
     * The database table used by the model.
     * @var string
     */
    protected $table = 'tenant_service_items';

    /**
     * Mass Assignable fields of model
     * @var array
     */
    protected $fillable = [
        'extra_cost',
        'package_id',
        'tenant_service_id',
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
