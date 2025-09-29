<?php

namespace App\Models\queuerequisition\Traits;

/**
 * Class DepartmentAttribute.
 */
trait QueueRequisitionAttribute
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
         '.$this->getViewButtonAttribute("manage-holiday", "biller.queuerequisitions.show").'
                '.$this->getEditButtonAttribute("edit-holiday", "biller.queuerequisitions.edit").'
                '.$this->getDeleteButtonAttribute("delete-holiday", "biller.queuerequisitions.destroy").'
                ';
    }
}
