<?php

namespace App\Models\promotions;

use Illuminate\Database\Eloquent\Model;

class EmailedCustomerPromoReservation extends Model
{

    protected $table = 'emailed_customer_promo_reservations';

    protected $primaryKey = 'id';

    protected $fillable = [
        'email_id'.
        'reservation_uuid'
    ];
}
