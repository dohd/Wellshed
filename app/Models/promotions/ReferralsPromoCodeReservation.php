<?php

namespace App\Models\promotions;

use App\Models\commission\CommissionItem;
use App\Models\customer\Customer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReferralsPromoCodeReservation extends Model
{

    protected $table = 'referral_promo_reservations';

    protected $primaryKey = 'uuid';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [

        'promo_code_id',
        'referer_uuid',
        'tier',
        'name',
        'message',
        'customer_id',
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


    public function promoCode(): BelongsTo
    {

        return $this->belongsTo(PromotionalCode::class, 'promo_code_id', 'id');
    }


    public function customer(): BelongsTo{

        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }


    public function customerReferer(): BelongsTo
    {

        return $this->belongsTo(CustomersPromoCodeReservation::class, 'referer_uuid', 'uuid');
    }

    public function thirdPartyReferer(): BelongsTo
    {

        return $this->belongsTo(ThirdPartiesPromoCodeReservation::class, 'referer_uuid', 'uuid');
    }

    public function referralReferer(): BelongsTo
    {

        return $this->belongsTo(ReferralsPromoCodeReservation::class, 'referer_uuid', 'uuid');
    }

    public function commission_item()
    {
        return $this->hasOne(CommissionItem::class, 'reserve_uuid', 'uuid');
    }

}
