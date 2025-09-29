<?php

namespace App\Models\leave\Traits;

trait LeaveAttribute
{
    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getViewButtonAttribute("manage-leave-application", "biller.leave.show")
        .' '. $this->getEditButtonAttribute("edit-leave-application", "biller.leave.edit")
        .' '.$this->getDeleteButtonAttribute("delete-leave-application", "biller.leave.destroy");     
    }
}
