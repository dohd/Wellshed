<?php

namespace App\Models\creditnote\Traits;

use App\Models\Access\User\User;
use App\Models\account\Account;
use App\Models\creditnote\CreditNoteItem;
use App\Models\currency\Currency;
use App\Models\customer\Customer;
use App\Models\invoice\Invoice;
use App\Models\items\TaxReportItem;
use App\Models\transaction\Transaction;

trait CreditNoteRelationship
{
    public function ledgerAccount() {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function items()
    {
        return $this->hasMany(CreditNoteItem::class);
    }

    public function credit_note_tax_reports()
    {
        return $this->hasMany(TaxReportItem::class, 'credit_note_id');
    }

    public function debit_note_tax_reports()
    {
        return $this->hasMany(TaxReportItem::class, 'debit_note_id');
    }

    public function debitnote_transactions()
    {
        return $this->hasMany(Transaction::class, 'dnote_id');
    }

    public function creditnote_transactions()
    {
        return $this->hasMany(Transaction::class, 'cnote_id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}