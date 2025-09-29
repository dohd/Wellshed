<?php

namespace App\Models\manualjournal\Traits;

use App\Models\account\Account;
use App\Models\customer\Customer;
use App\Models\invoice\Invoice;
use App\Models\invoice_payment\InvoicePayment;
use App\Models\items\JournalItem;
use App\Models\opening_stock\OpeningStock;
use App\Models\reconciliation\ReconciliationItem;
use App\Models\supplier\Supplier;
use App\Models\transaction\Transaction;
use App\Models\utility_bill\UtilityBill;

trait JournalRelationship
{
    public function openingStock()
    {
        return $this->belongsTo(OpeningStock::class, 'op_stock_id');
    }

    public function paid_invoice()
    {
        return $this->belongsTo(InvoicePayment::class, 'paid_invoice_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'man_journal_id');
    }

    public function bill()
    {
        return $this->hasOne(UtilityBill::class, 'man_journal_id');
    }

    public function reconciliation_items()
    {
        return $this->hasMany(ReconciliationItem::class, 'man_journal_id');
    }
    
    public function items()
    {
        return $this->hasMany(JournalItem::class, 'journal_id');
    }

    public function ledger_account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'man_journal_id');
    }
}
