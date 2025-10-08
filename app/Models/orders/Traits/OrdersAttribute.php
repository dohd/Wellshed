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
         '.$this->getViewButtonAttribute("manage-department", "biller.customer_orders.show").'
                '.$this->getEditButtonAttribute("edit-department", "biller.customer_orders.edit").'
                '.$this->getDeleteButtonAttribute("delete-department", "biller.customer_orders.destroy").'
                ';
    }
}
