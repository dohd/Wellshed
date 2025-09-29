<?php

namespace App\Models\job_valuation\Traits;

trait JobValuationAttribute
{
    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getViewButtonAttribute("manage-opening-stock", "biller.job_valuations.show")
        // .' '. $this->getEditButtonAttribute("edit-opening-stock", "biller.job_valuations.edit")
        .' '.$this->getDeleteButtonAttribute("delete-opening-stock", "biller.job_valuations.destroy");     
    }
}
