<?php

namespace App\Models\rjc\Traits;

/**
 * Class ProductcategoryAttribute.
 */
trait RjcAttribute
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
         '.$this->getViewButtonAttribute("manage-rjc", "biller.rjcs.show").'
                '.$this->getEditButtonAttribute("edit-rjc", "biller.rjcs.edit").'
                '.$this->getDeleteButtonAttribute("delete-rjc", "biller.rjcs.destroy").'
                ';
    }
}
