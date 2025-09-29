<?php

namespace App\Models\jobtitle\Traits;

/**
 * Class DepartmentAttribute.
 */
trait JobTitleAttribute
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
         '.$this->getViewButtonAttribute("manage-holiday", "biller.jobtitles.show").'
                '.$this->getEditButtonAttribute("edit-holiday", "biller.jobtitles.edit").'
                '.$this->getDeleteButtonAttribute("delete-holiday", "biller.jobtitles.destroy").'
                ';
    }
}
