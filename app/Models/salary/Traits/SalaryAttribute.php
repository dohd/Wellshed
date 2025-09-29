<?php

namespace App\Models\salary\Traits;

/**
 * Class DepartmentAttribute.
 */
trait SalaryAttribute
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
         '.$this->getViewButtonAttribute("manage-salary", "biller.salary.show").'
                '.$this->getEditButtonAttribute("edit-salary", "biller.salary.edit").'
                '.$this->getDeleteButtonAttribute("delete-salary", "biller.salary.destroy").'
                ';
    }
}
