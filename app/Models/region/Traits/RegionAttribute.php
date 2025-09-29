<?php

namespace App\Models\region\Traits;

/**
 * Class ProductcategoryAttribute.
 */
trait RegionAttribute
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
         '.$this->getViewButtonAttribute("manage-region", "biller.regions.show").'
                '.$this->getEditButtonAttribute("edit-region", "biller.regions.edit").'
                '.$this->getDeleteButtonAttribute("delete-region", "biller.regions.destroy").'
                ';
    }
}
