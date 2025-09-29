<?php

namespace App\Models\promotions;

use App\Models\commission\CommissionItem;
use App\Models\customer\Customer;
use App\Models\hrm\Hrm;
use App\Models\recentCustomer\RecentCustomerEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomersPromoCodeReservation extends Model
{

    protected $table = 'customers_promo_code_reservations';

    protected $primaryKey = 'uuid';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [

        'promo_code_id',
        'tier',
        'customer_id',
        'phone',
        'whatsapp_number',
        'email',
        'message',
        'status',
        'reserved_by',
        'reserved_at',
        'expires_at',
        'redeemable_code'
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('ins', function ($builder) {
            $builder->whereHas('promoCode');
        });
    }


    public function promoCode(): BelongsTo
    {

        return $this->belongsTo(PromotionalCode::class, 'promo_code_id', 'id');
    }

    public function customer(): BelongsTo{

        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function reserver(): BelongsTo
    {

        return $this->belongsTo(Hrm::class, 'reserved_by', 'id');
    }

    public function emailedReservation()
    {
        return $this->belongsToMany(RecentCustomerEmail::class, 'emailed_customer_promo_reservations', 'reservation_uuid', 'email_id')
            ->withTimestamps();
    }

    public function commission_item()
    {
        return $this->hasOne(CommissionItem::class, 'reserve_uuid', 'uuid');
    }
}
