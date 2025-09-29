<?php

namespace App\Models\payroll\Traits;

/**
 * Class payrollAttribute.
 */
trait PayrollAttribute
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
         '.$this->getViewButtonAttribute("manage-payroll", "biller.payroll.show").'
                '.$this->getEditButtonAttribute("edit-payroll", "biller.payroll.edit").'
                ';
    }
}
