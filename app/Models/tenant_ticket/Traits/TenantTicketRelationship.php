<?php

namespace App\Models\tenant_ticket\Traits;

use App\Models\Access\User\User;
use App\Models\tenant\Tenant;
use App\Models\tenant_ticket\TenantReply;
use App\Models\ticket_category\TicketCategory;

trait TenantTicketRelationship
{
    public function category()
    {
        return $this->belongsTo(TicketCategory::class, 'category_id');
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'ins');
    }

    public function replies()
    {
        return $this->hasMany(TenantReply::class)->orderBy('index', 'DESC');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
