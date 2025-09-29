<?php

namespace App\Models\refill_product\Traits;

trait RefillProductAttribute
{
    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getViewButtonAttribute("manage-refill-product", "biller.refill_products.show")
        .' '. $this->getEditButtonAttribute("edit-refill-product", "biller.refill_products.edit")
        .' '.$this->getDeleteButtonAttribute("delete-refill-product", "biller.refill_products.destroy");     
    }
}
