<?php

namespace App\Models\part\Traits;

/**
 * Class PartAttribute.
 */
trait PartAttribute
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
         '.$this->getViewButtonAttribute("manage-part", "biller.parts.show").'
                '.$this->getEditButtonAttribute("edit-part", "biller.parts.edit").'
                '.$this->getDeleteButtonAttribute("delete-part", "biller.parts.destroy").'
                ';
    }
}
