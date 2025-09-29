<?php

namespace App\Models\utility_bill\Traits;

use App\Models\advance_payment\AdvancePayment;
use App\Models\casual_labourer_remuneration\CasualLabourersRemuneration;
use App\Models\currency\Currency;
use App\Models\goodsreceivenote\Goodsreceivenote;
use App\Models\items\BillpaymentItem;
use App\Models\items\GoodsreceivenoteItem;
use App\Models\items\TaxReportItem;
use App\Models\items\UtilityBillItem;
use App\Models\project\Project;
use App\Models\purchase\Purchase;
use App\Models\supplier\Supplier;
use App\Models\transaction\Transaction;

trait UtilityBillRelationship
{   
    public function project()
    {
        return $this->hasOneThrough(Project::class, Transaction::class, 'bill_id', 'id', 'id', 'project_id');
    }

    public function casualRemun()
    {
        return $this->belongsTo(CasualLabourersRemuneration::class, 'casual_remun_id', 'clr_number');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'bill_id');
    }

    public function payments()
    {
        return $this->hasMany(BillpaymentItem::class, 'bill_id');
    }

    public function purchase_tax_reports()
    {
        return $this->hasMany(TaxReportItem::class, 'purchase_id');
    }
    
    public function advance_payment()
    {
        return $this->belongsTo(AdvancePayment::class, 'ref_id');
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'purchase_id');
    }

    public function grn()
    {
        return $this->belongsTo(Goodsreceivenote::class, 'grn_id');
    }

    public function grn_items()
    {
        return $this->hasManyThrough(GoodsreceivenoteItem::class, UtilityBillItem::class, 'bill_id', 'id', 'id', 'ref_id');
    }

    public function items()
    {
        return $this->hasMany(UtilityBillItem::class, 'bill_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
