<?php

namespace App\Models\tax_prn\Traits;

trait TaxPrnAttribute
{
    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getViewButtonAttribute("manage-invoice", "biller.tax_prns.show")
        .' '. $this->getEditButtonAttribute("edit-invoice", "biller.tax_prns.edit")
        .' '.$this->getDeleteButtonAttribute("delete-invoice", "biller.tax_prns.destroy");     
    }
}
