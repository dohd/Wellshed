<?php

namespace App\Models\account;

use App\Models\ModelTrait;
use Illuminate\Database\Eloquent\Model;
use App\Models\account\Traits\AccountAttribute;
use App\Models\account\Traits\AccountRelationship;

class Account extends Model
{
    use ModelTrait,
        AccountAttribute,
        AccountRelationship {
        // AccountAttribute::getEditButtonAttribute insteadof ModelTrait;
    }

    /**
     * NOTE : If you want to implement Soft Deletes in this model,
     * then follow the steps here : https://laravel.com/docs/5.4/eloquent#soft-deleting
     */

    /**
     * The database table used by the model.
     * @var string
     */
    protected $table = 'accounts';

    /**
     * Make custom attribute visible
     */
    protected $appends = ['has_sub_accounts'];

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
            $instance->user_id = $instance->user_id ?: auth()->user()->id;
            $instance->ins = $instance->ins ?: auth()->user()->ins;
            return $instance;
        });
        
        static::addGlobalScope('ins', function ($builder) {
            if (isset(auth()->user()->ins)) {
                $builder->where('ins', auth()->user()->ins);
            }
        });
        static::addGlobalScope('account_type_detail_id', function ($builder) {
            $builder->whereHas('account_type_detail');
        });
    }

    // Override resolveRouteBinding to bypass global scope
    public function resolveRouteBinding($value, $field = null)
    {
        return $this->withoutGlobalScopes(['account_type_detail_id'])->where($field ?? 'id', $value)->firstOrFail();
    }
}
