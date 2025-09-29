<?php

namespace App\Models\sale_return\Traits;

trait SaleReturnAttribute
{
    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getViewButtonAttribute("manage-opening-stock", "biller.sale_returns.show")
        .' '. $this->getEditButtonAttribute("edit-opening-stock", "biller.sale_returns.edit")
        .' '.$this->getDeleteButtonAttribute("delete-opening-stock", "biller.sale_returns.destroy");     
    }
}
