<?php

namespace App\Models\opening_stock\Traits;

trait OpeningStockAttribute
{
    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getViewButtonAttribute("manage-opening-stock", "biller.opening_stock.show")
        .' '. $this->getEditButtonAttribute("edit-opening-stock", "biller.opening_stock.edit")
        .' '.$this->getDeleteButtonAttribute("delete-opening-stock", "biller.opening_stock.destroy");     
    }
}
