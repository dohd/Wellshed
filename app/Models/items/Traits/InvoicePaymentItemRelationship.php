<?php

namespace App\Models\items\Traits;

use App\Models\account\Account;
use App\Models\customer\Customer;
use App\Models\invoice\Invoice;
use App\Models\invoice_payment\InvoicePayment;
use App\Models\items\PaidInvoiceItem;
use App\Models\items\WithholdingItem;
use App\Models\transaction\Transaction;

trait InvoicePaymentItemRelationship
{
    public function paid_invoice()
    {
        return $this->belongsTo(InvoicePayment::class, 'paidinvoice_id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function withholding_item()
    {
        return $this->hasOne(WithholdingItem::class, 'paid_invoice_item_id');
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
        return $this->hasMany(PaidInvoiceItem::class, 'paidinvoice_id');
    }
}
