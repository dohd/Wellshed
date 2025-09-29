<?php

namespace App\Models\client_vendor_ticket;

use App\Models\client_vendor_ticket\Traits\ClientVendorReplyRelationship;
use Illuminate\Database\Eloquent\Model;

class ClientVendorReply extends Model
{
    use ClientVendorReplyRelationship;
    
    /**
     * NOTE : If you want to implement Soft Deletes in this model,
     * then follow the steps here : https://laravel.com/docs/5.4/eloquent#soft-deleting
     */

    /**
     * The database table used by the model.
     * @var string
     */
    protected $table = 'client_vendor_replies';

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
            $instance->fill([
                'index' => ClientVendorReply::max('index')+1,
                'date' => date('Y-m-d'),
                'category' => !auth()->user()->is_tenant? 'Operator' : 'Owner',
                'ins' => auth()->user()->ins,
                'user_id' => auth()->user()->id,
            ]);
            return $instance;
        });
    }
}
