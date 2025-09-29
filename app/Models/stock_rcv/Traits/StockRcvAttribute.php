<?php

namespace App\Models\stock_rcv\Traits;

trait StockRcvAttribute
{
    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getViewButtonAttribute("manage-stock-transfer", "biller.stock_rcvs.show")
        .' '. $this->getEditButtonAttribute("edit-stock-transfer", "biller.stock_rcvs.edit")
        .' '.$this->getDeleteButtonAttribute("delete-stock-transfer", "biller.stock_rcvs.destroy");     
    }
}
