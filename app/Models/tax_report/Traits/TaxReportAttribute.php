<?php

namespace App\Models\tax_report\Traits;

trait TaxReportAttribute
{
    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getViewButtonAttribute("manage-account", "biller.tax_reports.show")
        .' '. $this->getEditButtonAttribute("edit-account", "biller.tax_reports.edit")
        .' '.$this->getDeleteButtonAttribute("delete-account", "biller.tax_reports.destroy");     
    }
}
