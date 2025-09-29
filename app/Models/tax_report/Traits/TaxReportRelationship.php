<?php

namespace App\Models\tax_report\Traits;

use App\Models\items\TaxReportItem;
use App\Models\tax_prn\TaxPrn;
use App\Models\tax_report\TaxReportPrn;

trait TaxReportRelationship
{
    public function items()
    {
        return $this->hasMany(TaxReportItem::class);
    }

    public function tax_prn()
    {
        return $this->hasOneThrough(TaxPrn::class, TaxReportPrn::class, 'tax_report_id', 'id', 'id', 'tax_prn_id')
            ->withoutGlobalScopes();
    }
}
