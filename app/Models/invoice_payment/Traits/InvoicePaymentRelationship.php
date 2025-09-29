<?php

namespace App\Models\invoice_payment\Traits;

use App\Models\account\Account;
use App\Models\currency\Currency;
use App\Models\customer\Customer;
use App\Models\invoice\Invoice;
use App\Models\invoice_payment\InvoicePayment;
use App\Models\items\InvoicePaymentItem;
use App\Models\manualjournal\Journal;
use App\Models\project\Project;
use App\Models\reconciliation\ReconciliationItem;
use App\Models\transaction\Transaction;

trait InvoicePaymentRelationship
{
    public function invoices() 
    {
        return $this->hasManyThrough(Invoice::class, InvoicePaymentItem::class, 'paidinvoice_id', 'id', 'id', 'invoice_id')
            ->withoutGlobalScopes();
    }

    // Related Payment
    public function relPayment()
    {
        return $this->belongsTo(InvoicePayment::class, 'rel_payment_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function journal()
    {
        return $this->hasOne(Journal::class, 'paid_invoice_id');
    }

    public function reconciliation_items()
    {
        return $this->hasMany(ReconciliationItem::class, 'deposit_id');
    }

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
        return $this->hasMany(InvoicePaymentItem::class, 'paidinvoice_id');
    }
}
