<?php

namespace App\Models\supplier;

use App\Models\ModelTrait;
use Illuminate\Database\Eloquent\Model;
use App\Models\supplier\Traits\SupplierAttribute;
use App\Models\supplier\Traits\SupplierRelationship;

class Supplier extends Model
{
    use ModelTrait,
        SupplierAttribute,
        SupplierRelationship {
        // SupplierAttribute::getEditButtonAttribute insteadof ModelTrait;
    }

    /**
     * NOTE : If you want to implement Soft Deletes in this model,
     * then follow the steps here : https://laravel.com/docs/5.4/eloquent#soft-deleting
     */

    /**
     * The database table used by the model.
     * @var string
     */
    protected $table = 'suppliers';

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

        static::creating(function ($instance) {
            $instance->ins = $instance->ins ?: auth()->user()->ins;
            $instance->user_id = $instance->user_id ?: auth()->user()->id;
            return $instance;
        });
        
        static::addGlobalScope('ins', function ($builder) {
            if (isset(auth()->user()->ins)) {
                $builder->where('ins', auth()->user()->ins);
            }
        });
        static::addGlobalScope('currency_id', function ($builder) {
            $builder->whereNotNull('currency_id');
        });
    }

    // Override resolveRouteBinding to bypass global scope
    public function resolveRouteBinding($value, $field = null)
    {
        return $this->withoutGlobalScopes(['currency_id'])->where($field ?? 'id', $value)->firstOrFail();
    }
    
    public function getPictureAttribute()
    {
        if (!$this->attributes['picture']) {
            return 'example.png';
        }

        return $this->attributes['picture'];
    }
}
