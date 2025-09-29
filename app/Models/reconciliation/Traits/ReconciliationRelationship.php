<?php

namespace App\Models\reconciliation\Traits;

use App\Models\Access\User\User;
use App\Models\account\Account;
use App\Models\reconciliation\ReconciliationItem;

trait ReconciliationRelationship
{
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function items()
    {
        return $this->hasMany(ReconciliationItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}