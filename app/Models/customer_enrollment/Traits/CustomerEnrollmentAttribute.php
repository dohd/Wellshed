<?php

namespace App\Models\customer_enrollment\Traits;

/**
 * Class CustomerEnrollmentAttribute.
 */
trait CustomerEnrollmentAttribute
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
         '.$this->getViewButtonAttribute("manage-promo-codes", "biller.customer_enrollments.show").'
                '.$this->getEditButtonAttribute("edit-customer_enrollment", "biller.customer_enrollments.edit").'
                '.$this->getDeleteButtonAttribute("delete-customer_enrollment", "biller.customer_enrollments.destroy").'
                ';
    }
}
