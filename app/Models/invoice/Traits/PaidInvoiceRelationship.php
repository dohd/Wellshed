<?php

namespace App\Models\invoice\Traits;

use App\Models\account\Account;
use App\Models\customer\Customer;
use App\Models\items\PaidInvoiceItem;
use App\Models\transaction\Transaction;

trait PaidInvoiceRelationship
{
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'deposit_id');
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(PaidInvoiceItem::class, 'paidinvoice_id');
    }
}
