<?php

namespace App\Models\purchase_requisition\Traits;

trait PurchaseRequisitionAttribute
{
    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getViewButtonAttribute("manage-purchase_requisition", "biller.purchase_requisitions.show")
        .' '. $this->getEditButtonAttribute("edit-purchase_requisition", "biller.purchase_requisitions.edit")
        .' '.$this->getDeleteButtonAttribute("delete-purchase_requisition", "biller.purchase_requisitions.destroy");     
    }
}
