<?php

namespace App\Models\projectequipment\Traits;

/**
 * Class ProductcategoryAttribute.
 */
trait ProjectEquipmentAttribute
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
         '.$this->getViewButtonAttribute("manage-equipment", "biller.regions.show").'
                '.$this->getEditButtonAttribute("edit-equipment", "biller.regions.edit").'
                '.$this->getDeleteButtonAttribute("delete-equipment", "biller.regions.destroy").'
                ';
    }
}
