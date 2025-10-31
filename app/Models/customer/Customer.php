<?php

namespace App\Models\customer;

use App\Models\ModelTrait;
use Illuminate\Database\Eloquent\Model;
use App\Models\customer\Traits\CustomerAttribute;
use App\Models\customer\Traits\CustomerRelationship;

class Customer extends Model
{
    use ModelTrait,
        CustomerAttribute,
        CustomerRelationship;

    /**
     * The database table used by the model.
     * @var string
     */
    protected $table = 'customers';

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

    /**
     * model life cycle event listeners
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        if (auth()->id()) {
            static::creating(function ($model) {
                $model->tid = Customer::max('tid')+1;
                $model->created_by = auth()->id();
                return $model;
            });

            static::updating(function ($model) {
                $model->updated_by = auth()->id();
                return $model;
            });
        }        
    }

    // Override resolveRouteBinding to bypass global scope
    public function resolveRouteBinding($value, $field = null)
    {
        // return $this->withoutGlobalScopes(['currency_id'])->where($field ?? 'id', $value)->firstOrFail();
    }

    /**
     * Set password attribute.
     *
     * @param [string] $password
     */
    public function setPasswordAttribute($password)
    {
        if (isset($password)) $this->attributes['password'] = bcrypt($password);
    }

    public function getPictureAttribute()
    {
        if (!$this->attributes['picture']) return 'example.png';
            
        return $this->attributes['picture'];
    }
}
