<?php

namespace App\Models\account\Traits;

use App\Models\account\AccountType;
use App\Models\account\AccountTypeDetail;
use App\Models\currency\Currency;
use App\Models\manualjournal\Journal;
use App\Models\reconciliation\Reconciliation;
use App\Models\transaction\Transaction;

/**
 * Class AccountRelationship
 */
trait AccountRelationship
{
    public function reconciliations()
    {
        return $this->hasMany(Reconciliation::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function account_type_detail()
    {
        return $this->belongsTo(AccountTypeDetail::class);
    }

    public function gen_journal()
    {
        return $this->hasOne(Journal::class);
    }

    public function accountType()
    {
        return $this->belongsTo(AccountType::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}