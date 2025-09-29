<?php

namespace App\Models\items\Traits;

use App\Models\creditnote\CreditNote;
use App\Models\invoice\Invoice;
use App\Models\tax_report\TaxReport;
use App\Models\utility_bill\UtilityBill;

trait TaxReportItemRelationship
{
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function credit_note()
    {
        return $this->belongsTo(CreditNote::class, 'credit_note_id')->whereNull('supplier_id');
    }

    public function purchase()
    {
        return $this->belongsTo(UtilityBill::class, 'purchase_id');
    }

    public function bill()
    {
        return $this->belongsTo(UtilityBill::class, 'purchase_id');
    }

    public function debit_note()
    {
        return $this->belongsTo(CreditNote::class, 'debit_note_id')->whereNull('customer_id');
    }

    public function tax_report()
    {
        return $this->belongsTo(TaxReport::class, 'tax_report_id');
    }
}
