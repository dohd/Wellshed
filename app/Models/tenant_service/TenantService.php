<?php

namespace App\Models\tenant_service;

use App\Models\ModelTrait;
use Illuminate\Database\Eloquent\Model;
use App\Models\tenant_service\Traits\TenantServiceAttribute;
use App\Models\tenant_service\Traits\TenantServiceRelationship;

class TenantService extends Model
{
    use ModelTrait, TenantServiceAttribute, TenantServiceRelationship;

    /**
     * NOTE : If you want to implement Soft Deletes in this model,
     * then follow the steps here : https://laravel.com/docs/5.4/eloquent#soft-deleting
     */

    /**
     * The database table used by the model.
     * @var string
     */
    protected $table = 'tenant_services';

    /**
     * Mass Assignable fields of model
     * @var array
     */
    protected $fillable = [
        'module_id',
        'name',
        'cost',
        'maintenance_cost',
        'maintenance_term',
        'extras_term',
        'total_cost',
        'extras_total',
        'user_id',
        'ins',
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

        static::creating(function($instance) {
            $instance->user_id = auth()->user()->id;
            $instance->ins = auth()->user()->ins;
            return $instance;
        });
    }
}
