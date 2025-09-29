<?php

namespace App\Models\holiday_list\Traits;

trait HolidayListAttribute
{
    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getViewButtonAttribute("manage-holiday", "biller.holiday_list.show")
        .' '. $this->getEditButtonAttribute("edit-holiday", "biller.holiday_list.edit")
        .' '.$this->getDeleteButtonAttribute("delete-holiday", "biller.holiday_list.destroy");     
    }
}
