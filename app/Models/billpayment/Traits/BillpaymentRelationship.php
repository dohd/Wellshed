<?php

namespace App\Models\billpayment\Traits;

use App\Models\account\Account;
use App\Models\items\BillpaymentItem;
use App\Models\reconciliation\ReconciliationItem;
use App\Models\supplier\Supplier;
use App\Models\transaction\Transaction;
use App\Models\utility_bill\UtilityBill;

trait BillpaymentRelationship
{
    public function reconciliation_items()
    {
        return $this->hasMany(ReconciliationItem::class, 'payment_id');
    }
    
    public function transactions() 
    {
        return $this->hasMany(Transaction::class, 'payment_id');
    }

    public function bills()
    {
        return $this->belongsToMany(UtilityBill::class, 'bill_payment_items', 'bill_payment_id', 'bill_id');
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
    
    public function items()
    {
        return $this->hasMany(BillpaymentItem::class, 'bill_payment_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
