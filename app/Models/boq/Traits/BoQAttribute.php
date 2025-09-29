<?php

namespace App\Models\boq\Traits;

/**
 * Class boqAttribute.
 */
trait BoQAttribute
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
         '.$this->getViewButtonAttribute("manage-boqs", "biller.boqs.show").'
                '.$this->getEditButtonAttribute("edit-boqs", "biller.boqs.edit").'
                '.$this->getDeleteButtonAttribute("delete-boqs", "biller.boqs.destroy").'
                ';
    }
}
