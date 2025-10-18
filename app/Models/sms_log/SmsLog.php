<?php

namespace App\Models\sms_log;

use App\Models\ModelTrait;
use App\Models\sms_log\Traits\SmsLogAttribute;
use App\Models\sms_log\Traits\SmsLogRelationship;
use Illuminate\Database\Eloquent\Model;


class SmsLog extends Model
{
    use ModelTrait,
        // SmsLogAttribute,
        SmsLogRelationship {
        // SmsLogAttribute::getEditButtonAttribute insteadof ModelTrait;
    }

    /**
     * NOTE : If you want to implement Soft Deletes in this model,
     * then follow the steps here : https://laravel.com/docs/5.4/eloquent#soft-deleting
     */

    /**
     * The database table used by the model.
     * @var string
     */
    protected $table = 'sms_logs';

    /**
     * Mass Assignable fields of model
     * @var array
     */
    protected $fillable = [
        'mobile', 'message', 'message_id', 'status', 'response'
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
