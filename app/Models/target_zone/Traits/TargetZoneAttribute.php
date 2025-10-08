<?php

namespace App\Models\target_zone\Traits;

/**
 * Class TargetZoneAttribute.
 */
trait TargetZoneAttribute
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
         '.$this->getViewButtonAttribute("manage-department", "biller.target_zones.show").'
                '.$this->getEditButtonAttribute("edit-department", "biller.target_zones.edit").'
                '.$this->getDeleteButtonAttribute("delete-department", "biller.target_zones.destroy").'
                ';
    }
}
