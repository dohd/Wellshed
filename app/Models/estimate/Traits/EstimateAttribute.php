<?php

namespace App\Models\estimate\Traits;

trait EstimateAttribute
{
    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getViewButtonAttribute("manage-opening-stock", "biller.estimates.show")
        .' '. $this->getEditButtonAttribute("edit-opening-stock", "biller.estimates.edit")
        .' '.$this->getDeleteButtonAttribute("delete-opening-stock", "biller.estimates.destroy");     
    }
}
