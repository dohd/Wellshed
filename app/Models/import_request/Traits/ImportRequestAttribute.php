<?php

namespace App\Models\import_request\Traits;

/**
 * Class ImportRequestAttribute.
 */
trait ImportRequestAttribute
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
         '.$this->getViewButtonAttribute("manage-import_request", "biller.import_requests.show").'
                '.$this->getEditButtonAttribute("edit-import_request", "biller.import_requests.edit").'
                '.$this->getDeleteButtonAttribute("delete-import_request", "biller.import_requests.destroy").'
                ';
    }
}
