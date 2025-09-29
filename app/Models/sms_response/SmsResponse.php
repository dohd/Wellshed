<?php

namespace App\Models\sms_response;

use App\Models\ModelTrait;
use App\Models\sms_response\Traits\SmsResponseAttribute;
use App\Models\sms_response\Traits\SmsResponseRelationship;
use Illuminate\Database\Eloquent\Model;

class SmsResponse extends Model
{
    use ModelTrait,
        SmsResponseAttribute,
        SmsResponseRelationship {
    }

    /**
     * NOTE : If you want to implement Soft Deletes in this model,
     * then follow the steps here : https://laravel.com/docs/5.4/eloquent#soft-deleting
     */

    /**
     * The database table used by the model.
     * @var string
     */
    protected $table = 'sms_responses';

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
            $user = auth()->user();
            if ($user) {
                $instance->user_id = $user->id;
                $instance->ins = $user->ins;
            } else {
                // Handle unauthenticated case if needed
                $instance->user_id = 2;
                $instance->ins = 2;
            }
        });

        // static::addGlobalScope('ins', function ($builder) {
        //     $builder->where('ins', auth()->user()->ins);
        // });
    }
}
