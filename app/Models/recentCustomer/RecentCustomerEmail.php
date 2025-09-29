<?php

namespace App\Models\recentCustomer;

use App\Models\promotions\CustomersPromoCodeReservation;
use App\Models\promotions\ThirdPartiesPromoCodeReservation;
use App\Models\customer\Customer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecentCustomerEmail extends Model
{
    //
    protected $table = 'recent_customer_emails';

    protected $fillable = [

        'customer_id',
        'email_address',
        'subject',
        'content',
        'created_by',
        'prospect_name',
    ];

    public function customer(): BelongsTo{

        return $this->belongsTo(Customer::class);
    }


    protected static function boot()
    {
        parent::boot();

        static::creating(function ($instance) {

            $instance->ins = auth()->user()->ins;
            return $instance;
        });


        static::addGlobalScope('ins', function ($builder) {

            $builder->where('ins', '=', auth()->user()->ins);
        });
    }


    public function customerPromoCodeReservations()
    {
        return $this->belongsToMany(CustomersPromoCodeReservation::class, 'emailed_customer_promo_reservations', 'email_id', 'reservation_uuid')
            ->withTimestamps();
    }

    public function thirdPartyPromoCodeReservations()
    {
        return $this->belongsToMany(ThirdPartiesPromoCodeReservation::class, 'emailed_third_party_promo_reservations', 'email_id', 'reservation_uuid')
            ->withTimestamps();
    }

}
