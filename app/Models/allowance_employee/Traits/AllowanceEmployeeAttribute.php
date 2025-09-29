<?php

namespace App\Models\allowance_employee\Traits;

/**
 * Class AllowanceAttribute.
 */
trait AllowanceEmployeeAttribute
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
         '.$this->getViewButtonAttribute("manage-department", "biller.allowances.show").'
                '.$this->getEditButtonAttribute("edit-department", "biller.allowances.edit").'
                '.$this->getDeleteButtonAttribute("delete-department", "biller.allowances.destroy").'
                ';
    }
}
