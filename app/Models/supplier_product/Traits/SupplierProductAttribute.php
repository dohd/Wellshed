<?php

namespace App\Models\supplier_product\Traits;

trait SupplierProductAttribute
{
    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getViewButtonAttribute("manage-pricelist", "biller.pricelistsSupplier.show")
        .' '. $this->getEditButtonAttribute("edit-pricelist", "biller.pricelistsSupplier.edit")
        .' '.$this->getDeleteButtonAttribute("delete-pricelist", "biller.pricelistsSupplier.destroy");     
    }
}
