<?php

namespace App\Models\purchase_request\Traits;

trait PurchaseRequestAttribute
{
    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getViewButtonAttribute("manage-purchase", "biller.purchase_requests.show")
        .' '. $this->getEditButtonAttribute("edit-purchase", "biller.purchase_requests.edit")
        .' '.$this->getDeleteButtonAttribute("delete-purchase", "biller.purchase_requests.destroy");     
    }
}
