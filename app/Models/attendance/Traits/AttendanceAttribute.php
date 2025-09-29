<?php

namespace App\Models\attendance\Traits;

trait AttendanceAttribute
{
    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getViewButtonAttribute("manage-attendance", "biller.attendances.show")
        // .' '. $this->getEditButtonAttribute("attendance-edit", "biller.attendances.edit")
        .' '.$this->getDeleteButtonAttribute("delete-attendance", "biller.attendances.destroy");     
    }
}
