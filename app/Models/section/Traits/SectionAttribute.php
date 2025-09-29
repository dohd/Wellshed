<?php

namespace App\Models\section\Traits;

/**
 * Class ProductcategoryAttribute.
 */
trait SectionAttribute
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
         '.$this->getViewButtonAttribute("manage-task", "biller.regions.show").'
                '.$this->getEditButtonAttribute("edit-task", "biller.regions.edit").'
                '.$this->getDeleteButtonAttribute("delete-task", "biller.regions.destroy").'
                ';
    }
}
