<?php

namespace App\Models\wage_item\Traits;

/**
 * Class WageItemAttribute
 */
trait WageItemAttribute
{
    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return
            // $this->getViewButtonAttribute("manage-payroll", "biller.wage_items.show") .' '.
            $this->getEditButtonAttribute("edit-payroll", "biller.wage_items.edit") .' '.
            $this->getDeleteButtonAttribute("delete-payroll", "biller.wage_items.destroy");
    }
}
