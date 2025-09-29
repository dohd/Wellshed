<?php

namespace App\Models\task_schedule\Traits;


trait TaskScheduleAttribute
{
    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getViewButtonAttribute("manage-schedule", "biller.taskschedules.show") 
        . ' ' . $this->getEditButtonAttribute("edit-schedule", "biller.taskschedules.edit")
        . ' ' . $this->getDeleteButtonAttribute("delete-schedule", "biller.taskschedules.destroy");
    }
}
