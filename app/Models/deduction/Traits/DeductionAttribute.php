<?php

namespace App\Models\deduction\Traits;

/**
 * Class DepartmentAttribute.
 */
trait DeductionAttribute
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
         '.$this->getViewButtonAttribute("manage-holiday", "biller.deductions.show").'
                '.$this->getEditButtonAttribute("edit-holiday", "biller.deductions.edit").'
                '.$this->getDeleteButtonAttribute("delete-holiday", "biller.deductions.destroy").'
                ';
    }
}
