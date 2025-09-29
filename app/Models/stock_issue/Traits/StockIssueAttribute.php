<?php

namespace App\Models\stock_issue\Traits;

trait StockIssueAttribute
{
    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getViewButtonAttribute("manage-opening-stock", "biller.stock_issues.show")
        .' '. $this->getEditButtonAttribute("edit-opening-stock", "biller.stock_issues.edit")
        .' '.$this->getDeleteButtonAttribute("delete-opening-stock", "biller.stock_issues.destroy");     
    }
}
