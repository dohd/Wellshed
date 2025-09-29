<?php

namespace App\Models\refill_product_category\Traits;

trait RefillProductCategoryAttribute
{
    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getViewButtonAttribute("manage-refill-product-category", "biller.refill_product_categories.show")
        .' '. $this->getEditButtonAttribute("edit-refill-product-category", "biller.refill_product_categories.edit")
        .' '.$this->getDeleteButtonAttribute("delete-refill-product-category", "biller.refill_product_categories.destroy");     
    }
}
