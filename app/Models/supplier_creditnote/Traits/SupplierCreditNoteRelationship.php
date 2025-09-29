<?php

namespace App\Models\supplier_creditnote\Traits;

use App\Models\currency\Currency;
use App\Models\goodsreceivenote\Goodsreceivenote;
use App\Models\supplier\Supplier;
use App\Models\supplier_creditnote\SupplierCreditNoteItem;
use App\Models\utility_bill\UtilityBill;

trait SupplierCreditNoteRelationship
{
    public function items()
    {
        return $this->hasMany(SupplierCreditNoteItem::class, 'supplier_creditnote_id');
    }
    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }
    public function bill()
    {
        return $this->belongsTo(UtilityBill::class, 'bill_id');
    }
    public function grn()
    {
        return $this->belongsTo(Goodsreceivenote::class, 'grn_id');
    }
    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }
}