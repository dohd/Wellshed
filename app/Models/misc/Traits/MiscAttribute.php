<?php

namespace App\Models\misc\Traits;

/**
 * Class MiscAttribute.
 */
trait MiscAttribute
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
         '.$this->getViewButtonAttribute("manage-misc", "biller.miscs.show").'
                '.$this->getEditButtonAttribute("edit-misc", "biller.miscs.edit").'
                '.$this->getDeleteButtonAttribute("delete-misc", "biller.miscs.destroy",'table').'
                ';
    }
}
