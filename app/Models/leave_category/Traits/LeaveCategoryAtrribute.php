<?php

namespace App\Models\leave_category\Traits;

trait LeaveCategoryAtrribute
{
    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getViewButtonAttribute("manage-leave", "biller.leave_category.show")
        .' '. $this->getEditButtonAttribute("edit-leave", "biller.leave_category.edit")
        .' '.$this->getDeleteButtonAttribute("delete-leave", "biller.leave_category.destroy");     
    }
}
