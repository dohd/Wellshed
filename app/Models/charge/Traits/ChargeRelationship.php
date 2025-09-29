<?php

namespace App\Models\charge\Traits;

use App\Models\transaction\Transaction;

trait ChargeRelationship
{
    public function reconcilitaion_item()
    {
        return $this->hasOne(ReconciliationItem::class);
    }
    
    public function bank()
    {
        return $this->belongsTo('App\Models\account\Account', 'bank_id');
    }

    public function expense_account()
    {
        return $this->belongsTo('App\Models\account\Account', 'expense_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'charge_id');
    }
}