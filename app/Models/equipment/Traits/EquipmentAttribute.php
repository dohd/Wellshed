<?php

namespace App\Models\equipment\Traits;

/**
 * Class OrderAttribute.
 */
trait EquipmentAttribute
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
         '.$this->getViewButtonAttribute("manage-equipment", "biller.equipments.show").'
                '.$this->getEditButtonAttribute("edit-equipment", "biller.equipments.edit").'
                '.$this->getDeleteButtonAttribute("delete-equipment", "biller.equipments.destroy").'
                ';


        
    }
}
