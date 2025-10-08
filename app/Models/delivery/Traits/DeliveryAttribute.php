<?php

namespace App\Models\delivery\Traits;

/**
 * Class DeliveryAttribute.
 */
trait DeliveryAttribute
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
         '.$this->getViewButtonAttribute("manage-department", "biller.deliveries.show").'
                '.$this->getEditButtonAttribute("edit-department", "biller.deliveries.edit").'
                '.$this->getDeleteButtonAttribute("delete-department", "biller.deliveries.destroy").'
                ';
    }
}
