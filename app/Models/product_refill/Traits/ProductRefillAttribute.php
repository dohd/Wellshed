<?php

namespace App\Models\product_refill\Traits;

trait ProductRefillAttribute
{
    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getViewButtonAttribute("manage-refill", "biller.product_refills.show")
        .' '. $this->getEditButtonAttribute("edit-refill", "biller.product_refills.edit")
        .' '.$this->getDeleteButtonAttribute("delete-refill", "biller.product_refills.destroy");     
    }
}
