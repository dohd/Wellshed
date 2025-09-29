<?php

namespace App\Models\key_activity\Traits;

/**
 * Class KeyActivityAttribute.
 */
trait KeyActivityAttribute
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
         '.$this->getViewButtonAttribute("manage-key_activity", "biller.key_activities.show").'
                '.$this->getEditButtonAttribute("edit-key_activity", "biller.key_activities.edit").'
                '.$this->getDeleteButtonAttribute("delete-key_activity", "biller.key_activities.destroy").'
                ';
    }
}
