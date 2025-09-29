<?php

namespace App\Models\customer_complain\Traits;

/**
 * Class CustomerComplainAttribute.
 */
trait CustomerComplainAttribute
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
         '.$this->getViewButtonAttribute("manage-customer_complain", "biller.customer_complains.show").'
                '.$this->getEditButtonAttribute("edit-customer_complain", "biller.customer_complains.edit").'
                '.$this->getDeleteButtonAttribute("delete-customer_complain", "biller.customer_complains.destroy").'
                ';
    }
}
