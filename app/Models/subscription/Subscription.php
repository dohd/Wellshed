<?php

namespace App\Models\subscription;

use App\Models\ModelTrait;
use Illuminate\Database\Eloquent\Model;
use App\Models\subscription\Traits\CustomerAttribute;
use App\Models\subscription\Traits\SubscriptionRelationship;
use Carbon\Carbon;

class Subscription extends Model
{
    use ModelTrait,
        CustomerAttribute,
        SubscriptionRelationship;

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

        static::creating(function ($model) {
            if (auth()->id()) {
                $model->ins = auth()->user()->ins;
                $model->created_by = auth()->id();                
            }
            $model->tid = Subscription::max('tid')+1;
            return $model;
        });

        if (auth()->id()) {
            static::updating(function ($model) {
                $model->updated_by = auth()->user()->id;
                return $model;
            });
        }
        
        static::addGlobalScope('ins', function ($builder) {
            if (isset(auth()->user()->ins))
            $builder->where('ins', auth()->user()->ins);
        });
        
        static::addGlobalScope('deleted_at', function ($builder) {
            $builder->whereNull('deleted_at');
        });
    }

    public function isExpired()
    {
        if (! $this->end_date) {
            return false; // or true, depending on your business rule
        }

        return Carbon::now()->gt(Carbon::parse($this->end_date));
    }
}
