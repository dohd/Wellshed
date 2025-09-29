<?php

namespace App\Models\stock_adj\Traits;

trait StockAdjAttribute
{
    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {        
        return $this->getViewButtonAttribute("manage-stock-adj", "biller.stock_adjs.show")
        .' '. $this->getEditButtonAttribute("edit-stock-adj", "biller.stock_adjs.edit")
        .' '.$this->getDeleteButtonAttribute("delete-stock-adj", "biller.stock_adjs.destroy");     
    }

    /**
     * Adjustment Type Attribute
     */
    public function getAdjustmentTypeAttribute()
    {
        if ($this->adj_type == 'Qty') $label = 'Quantity';
        if ($this->adj_type == 'Qty-Cost') $label = 'Cost & Quantity';
        return $label;
    }
}
