<?php

namespace App\Models\tax_prn\Traits;

use App\Models\tax_report\TaxReportPrn;

trait TaxPrnRelationship
{
    public function tax_reports()
    {
        return $this->belongsToMany(TaxReportPrn::class, 'tax_report_prn')->withoutGlobalScopes();
    }
}
