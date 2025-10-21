<?php

namespace App\Models\subscription;

use App\Models\ModelTrait;
use Illuminate\Database\Eloquent\Model;
use App\Models\subscription\Traits\CustomerAttribute;
use App\Models\subscription\Traits\CustomerRelationship;

class Subscription extends Model
{
    use ModelTrait,
        CustomerAttribute,
        CustomerRelationship;

    /**
     * The database table used by the model.
     * @var string
     */
    protected $table = 'subscriptions';

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
            static::creating(function ($instance) {
                $instance->ins = auth()->user()->ins;
                $instance->created_by = auth()->user()->id;
                return $instance;
            });
        }

        static::addGlobalScope('ins', function ($builder) {
            if (isset(auth()->user()->ins)) {
                $builder->where('ins', auth()->user()->ins);
            }
        });
    }

    // Override resolveRouteBinding to bypass global scope
    public function resolveRouteBinding($value, $field = null)
    {
        return $this->withoutGlobalScopes(['currency_id'])->where($field ?? 'id', $value)->firstOrFail();
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
