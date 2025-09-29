<?php

namespace App\Models\banktransfer\Traits;

use App\Models\reconciliation\ReconciliationItem;
use App\Models\transaction\Transaction;

/**
 * Class TransactionRelationship
 */
trait BanktransferRelationship
{
    public function reconciliation_item()
    {
        return $this->hasOne(ReconciliationItem::class, 'bank_transfer_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'bank_transfer_id');
    }

    public function source_account()
    {
        return $this->belongsTo('App\Models\account\Account', 'account_id');
    }

    public function dest_account()
    {
        return $this->belongsTo('App\Models\account\Account', 'debit_account_id');
    }

    public function account()
    {
        return $this->belongsTo('App\Models\account\Account');
    }
}
