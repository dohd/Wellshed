<?php

namespace App\Models\client_vendor_ticket;

use App\Models\client_vendor_ticket\Traits\ClientVendorTicketAttribute;
use App\Models\client_vendor_ticket\Traits\ClientVendorTicketRelationship;
use App\Models\ModelTrait;
use Illuminate\Database\Eloquent\Model;

class ClientVendorTicket extends Model
{
    use ModelTrait, ClientVendorTicketAttribute, ClientVendorTicketRelationship;

    /**
     * NOTE : If you want to implement Soft Deletes in this model,
     * then follow the steps here : https://laravel.com/docs/5.4/eloquent#soft-deleting
     */

    /**
     * The database table used by the model.
     * @var string
     */
    protected $table = 'client_vendor_tickets';

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
        static::addGlobalScope('ins', function ($builder) {
            if (isset(auth()->user()->ins)) {
                $builder->where('ins', auth()->user()->ins);
            }
        });

        static::creating(function ($instance) {
            $instance->fill([
                'customer_id' => auth()->user()->customer_id,
                'tid' => ClientVendorTicket::max('tid')+1,
                'date' => date('Y-m-d'),
                'ins' => auth()->user()->ins,
                'user_id' => auth()->user()->id,
            ]);
            return $instance;
        });
    }
}
