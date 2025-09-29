<?php

namespace App\Models\promotions;

use App\Models\commission\CommissionItem;
use App\Models\customer\Customer;
use App\Models\hrm\Hrm;
use App\Models\recentCustomer\RecentCustomerEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ThirdPartiesPromoCodeReservation extends Model
{

    protected $table = 'third_parties_promo_code_reservations';

    protected $primaryKey = 'uuid';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [

        'uuid',
        'promo_code_id',
        'tier',
        'name',
        'customer_id',
        'message',
        'organization',
        'phone',
        'whatsapp_number',
        'email',
        'status',
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

    public function customer(): BelongsTo{

        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }


    public function promoCode(): BelongsTo
    {

        return $this->belongsTo(PromotionalCode::class, 'promo_code_id', 'id');
    }

    public function reserver(): BelongsTo
    {

        return $this->belongsTo(Hrm::class, 'reserved_by', 'id');
    }

    public function emailedReservation(): BelongsToMany
    {
        return $this->belongsToMany(RecentCustomerEmail::class, 'emailed_third_party_promo_reservations', 'reservation_uuid', 'email_id')
            ->withTimestamps();
    }

    public function commission_item()
    {
        return $this->belongsTo(CommissionItem::class, 'reserve_uuid', 'uuid');
    }

}
