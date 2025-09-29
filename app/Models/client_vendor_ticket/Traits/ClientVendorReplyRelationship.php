<?php

namespace App\Models\client_vendor_ticket\Traits;

use App\Models\Access\User\User;
use App\Models\client_vendor_ticket\ClientVendorTicket;

trait ClientVendorReplyRelationship
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ticket()
    {
        return $this->belongsTo(ClientVendorTicket::class);
    }
}
