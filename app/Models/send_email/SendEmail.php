<?php

namespace App\Models\send_email;

use App\Models\ModelTrait;
use App\Models\send_email\Traits\SendEmailAttribute;
use App\Models\send_email\Traits\SendEmailRelationship;
use Illuminate\Database\Eloquent\Model;


class SendEmail extends Model
{
    use ModelTrait,
        SendEmailAttribute,
        SendEmailRelationship {
        // SendEmailAttribute::getEditButtonAttribute insteadof ModelTrait;
    }

    /**
     * NOTE : If you want to implement Soft Deletes in this model,
     * then follow the steps here : https://laravel.com/docs/5.4/eloquent#soft-deleting
     */

    /**
     * The database table used by the model.
     * @var string
     */
    protected $table = 'send_emails';

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
            if(auth()->user()){
                $instance->user_id = auth()->user()->id;
                $instance->ins = auth()->user()->ins;
            }else{
                $instance->user_id = 2;
            }
            return $instance;
        });

        static::addGlobalScope('ins', function ($builder) {
            if (isset(auth()->user()->ins)) {
                $builder->where('ins', auth()->user()->ins);
            }
        });
    }

}
