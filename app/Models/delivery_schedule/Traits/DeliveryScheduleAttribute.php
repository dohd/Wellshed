<?php

namespace App\Models\delivery_schedule\Traits;

/**
 * Class DeliveryScheduleAttribute.
 */
trait DeliveryScheduleAttribute
{
    // Make your attributes functions here
    // Further, see the documentation : https://laravel.com/docs/5.4/eloquent-mutators#defining-an-accessor


    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return '
         '.$this->getViewButtonAttribute("manage-department", "biller.delivery_schedules.show").'
                '.$this->getEditButtonAttribute("edit-department", "biller.delivery_schedules.edit").'
                '.$this->getDeleteButtonAttribute("delete-department*", "biller.delivery_schedules.destroy").'
                ';
    }
}
