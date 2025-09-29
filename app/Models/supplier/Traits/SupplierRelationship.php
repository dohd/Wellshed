<?php

namespace App\Models\supplier\Traits;

use App\Models\account\Account;
use App\Models\billpayment\Billpayment;
use App\Models\creditnote\CreditNote;
use App\Models\currency\Currency;
use App\Models\goodsreceivenote\Goodsreceivenote;
use App\Models\items\PaidbillItem;
use App\Models\manualjournal\Journal;
use App\Models\purchaseorder\Purchaseorder;
use App\Models\purchase\Purchase;
use App\Models\utility_bill\UtilityBill;
use App\Models\supplier_product\SupplierProduct;
use App\Models\transaction\Transaction;

/**
 * Class SupplierRelationship
 */
trait SupplierRelationship
{
    public function billPayments() 
    {
        return $this->hasMany(Billpayment::class)->whereNull('rel_payment_id');
    }

    public function ap_account() {
        return $this->belongsTo(Account::class, 'ap_account_id');
    }

    public function currency() {
        return $this->belongsTo(Currency::class);
    }

    public function journal() {
        return $this->hasOne(Journal::class);
    }
    
    public function debit_notes()
    {
        return $this->hasMany(CreditNote::class);
    }

    public function payments()
    {
        return $this->hasManyThrough(PaidbillItem::class, UtilityBill::class, 'supplier_id', 'bill_id');
    }

    public function bills()
    {
        return $this->hasMany(UtilityBill::class);
    }

    public function goods_receive_notes()
    {
        return $this->hasMany(Goodsreceivenote::class);
    }

    public function purchase_orders()
    {
        return $this->hasMany(Purchaseorder::class);
    }
    public function products()
    {
        return $this->hasMany(SupplierProduct::class);
    }
    public function supplier_products()
    {
        return $this->hasMany(SupplierProduct::class);
    }
    
    function purchase() {
        return $this->hasMany(Purchase::class);
    }
    function transactions() {
        return $this->hasMany(Transaction::class, 'supplier_id');
    }
}