<?php

namespace App\Models\promotions;

use Illuminate\Database\Eloquent\Model;

class EmailedThirdPartyPromoReservation extends Model
{

    protected $table = 'emailed_third_party_promo_reservations';

    protected $primaryKey = 'id';

    protected $fillable = [
        'email_id'.
        'reservation_uuid'
    ];
}
