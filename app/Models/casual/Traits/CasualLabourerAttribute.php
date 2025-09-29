<?php

namespace App\Models\casual\Traits;

/**
 * Class CasualLabourerAttribute.
 */
trait CasualLabourerAttribute
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
         '.$this->getViewButtonAttribute("manage-casuals", "biller.casuals.show").'
                '.$this->getEditButtonAttribute("edit-casuals", "biller.casuals.edit").'
                '.$this->getDeleteButtonAttribute("delete-casuals", "biller.casuals.destroy").'
                ';
    }
}
