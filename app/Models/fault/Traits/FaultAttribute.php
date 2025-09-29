<?php

namespace App\Models\fault\Traits;

/**
 * Class DepartmentAttribute.
 */
trait FaultAttribute
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
         '.$this->getViewButtonAttribute("manage-holiday", "biller.faults.show").'
                '.$this->getEditButtonAttribute("edit-holiday", "biller.faults.edit").'
                '.$this->getDeleteButtonAttribute("delete-holiday", "biller.faults.destroy").'
                ';
    }
}
