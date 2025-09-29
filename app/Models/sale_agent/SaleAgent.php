<?php

namespace App\Models\sale_agent;

use App\Models\ModelTrait;
use App\Models\sale_agent\Traits\SaleAgentAttribute;
use App\Models\sale_agent\Traits\SaleAgentRelationship;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SaleAgent extends Model
{
    use ModelTrait,
        SaleAgentAttribute,
        SaleAgentRelationship {
        // SaleAgentAttribute::getEditButtonAttribute insteadof ModelTrait;
    }

    /**
     * NOTE : If you want to implement Soft Deletes in this model,
     * then follow the steps here : https://laravel.com/docs/5.4/eloquent#soft-deleting
     */

    /**
     * The database table used by the model.
     * @var string
     */
    protected $table = 'sales_agents';

    /**
     * Mass Assignable fields of model
     * @var array
     */
    protected $fillable = [
        'uuid','public_code','first_name','last_name','date_of_birth','name','email','phone','county','city','referral_code',
        'onboarding_token','otp_code','otp_expires_at','is_phone_verified',
        'consent_terms','consent_data','status','alternative_number'
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
        static::creating(function ($model) {
            if (!$model->uuid) $model->uuid = (string) Str::uuid();
            if (!$model->onboarding_token) $model->onboarding_token = Str::random(60);
            // if (!$model->public_code) $model->public_code = self::generatePublicCode();
        });

        // static::addGlobalScope('ins', function ($builder) {
        //     if (isset(auth()->user()->ins)) {
        //         $builder->where('ins', auth()->user()->ins);
        //     }
        // });
    }

    public static function generatePublicCode($length = 8)
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // avoid I,O,0,1
        $max = strlen($alphabet) - 1;
        do {
            $code = '';
            for ($i=0; $i<$length; $i++) {
                $code .= $alphabet[random_int(0, $max)];
            }
        } while (self::where('public_code', $code)->exists());
        return $code;
    }

}
