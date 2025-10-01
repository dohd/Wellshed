<?php

namespace App\Models\orders\Traits;

/**
 * Class OrdersAttribute.
 */
trait OrdersAttribute
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
         '.$this->getViewButtonAttribute("manage-order", "biller.orders.show").'
                '.$this->getEditButtonAttribute("edit-order", "biller.orders.edit").'
                '.$this->getDeleteButtonAttribute("delete-order", "biller.orders.destroy").'
                ';
    }
}
