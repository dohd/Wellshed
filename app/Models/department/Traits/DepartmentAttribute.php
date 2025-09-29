<?php

namespace App\Models\department\Traits;

/**
 * Class DepartmentAttribute.
 */
trait DepartmentAttribute
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
         '.$this->getViewButtonAttribute("manage-department", "biller.departments.show").'
                '.$this->getEditButtonAttribute("edit-department", "biller.departments.edit").'
                '.$this->getDeleteButtonAttribute("delete-department", "biller.departments.destroy").'
                ';
    }
}
