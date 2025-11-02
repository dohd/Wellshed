<?php

namespace App\Models\payment_receipt\Traits;

/**
 * Class ProductcategoryAttribute.
 */
trait BranchAttribute
{
    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return 
            // $this->getViewButtonAttribute("manage-branch", "biller.branches.show")
            // . ' ' . $this->getEditButtonAttribute("edit-branch", "biller.branches.edit") . ' ' .
             $this->getDeleteButtonAttribute("delete-branch", "biller.branches.destroy");
    }
}