<?php

namespace App\Models\stock_transfer\Traits;

trait StockTransferAttribute
{
    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getViewButtonAttribute("manage-stock-transfer", "biller.stock_transfers.show")
        .' '. $this->getEditButtonAttribute("edit-stock-transfer", "biller.stock_transfers.edit")
        .' '.$this->getDeleteButtonAttribute("delete-stock-transfer", "biller.stock_transfers.destroy");     
    }
}
