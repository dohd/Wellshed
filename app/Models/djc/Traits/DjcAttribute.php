<?php

namespace App\Models\djc\Traits;

/**
 * Class ProductcategoryAttribute.
 */
trait DjcAttribute
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
         '.$this->getViewButtonAttribute("manage-djc", "biller.djcs.show").'
                '.$this->getEditButtonAttribute("edit-djc", "biller.djcs.edit").'
                '.$this->getDeleteButtonAttribute("delete-djc", "biller.djcs.destroy").'
                ';
    }
}
