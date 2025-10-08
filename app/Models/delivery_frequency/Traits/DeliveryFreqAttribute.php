<?php

namespace App\Models\delivery_frequency\Traits;

/**
 * Class DeliveryFreqAttribute.
 */
trait DeliveryFreqAttribute
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
         '.$this->getViewButtonAttribute("manage-department", "biller.delivery_frequencies.show").'
                '.$this->getEditButtonAttribute("edit-department", "biller.delivery_frequencies.edit").'
                '.$this->getDeleteButtonAttribute("delete-department", "biller.delivery_frequencies.destroy").'
                ';
    }
}
