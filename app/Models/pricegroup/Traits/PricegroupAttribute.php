<?php

namespace App\Models\pricegroup\Traits;

/**
 * Class WarehouseAttribute.
 */
trait PricegroupAttribute
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
         '.$this->getViewButtonAttribute("manage-product", "biller.pricegroups.show").'
                '.$this->getEditButtonAttribute("create-product", "biller.pricegroups.edit").'
                '.$this->getDeleteButtonAttribute("delete-product", "biller.pricegroups.destroy").'
                ';
    }
}
