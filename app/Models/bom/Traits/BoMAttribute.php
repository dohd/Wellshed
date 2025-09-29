<?php

namespace App\Models\bom\Traits;

/**
 * Class BoMAttribute.
 */
trait BoMAttribute
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
         '.$this->getViewButtonAttribute("manage-boms", "biller.boms.show").'
                '.$this->getEditButtonAttribute("edit-boms", "biller.boms.edit").'
                '.$this->getDeleteButtonAttribute("delete-boms", "biller.boms.destroy").'
                ';
    }
}
