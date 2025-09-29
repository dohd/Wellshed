<?php

namespace App\Models\warehouse\Traits;

/**
 * Class WarehouseAttribute.
 */
trait WarehouseAttribute
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
         '.$this->getViewButtonAttribute("manage-warehouse", "biller.warehouses.show").'
                '.$this->getEditButtonAttribute("edit-warehouse", "biller.warehouses.edit").'
                '.$this->getDeleteButtonAttribute("delete-warehouse", "biller.warehouses.destroy").'
                ';
    }
}
