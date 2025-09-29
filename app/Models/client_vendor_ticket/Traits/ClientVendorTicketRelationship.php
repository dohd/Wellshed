<?php

namespace App\Models\client_vendor_ticket\Traits;

use App\Models\Access\User\User;
use App\Models\client_vendor_tag\ClientVendorTag;
use App\Models\client_vendor_ticket\ClientVendorReply;
use App\Models\customer\Customer;
use App\Models\equipmentcategory\EquipmentCategory;

trait ClientVendorTicketRelationship
{
    public function tag()
    {
        return $this->belongsTo(ClientVendorTag::class, 'tag_id');
    }

    public function category()
    {
        return $this->belongsTo(EquipmentCategory::class, 'equip_categ_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function replies()
    {
        return $this->hasMany(ClientVendorReply::class)->orderBy('index', 'DESC');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
