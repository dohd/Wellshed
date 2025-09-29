<?php

namespace App\Models\project\Traits;

/**
 * Class TaskAttribute.
 */
trait TaskAttribute
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
         '.$this->getViewButtonAttribute("manage-task", "biller.tasks.show").'
                '.$this->getEditButtonAttribute("edit-task", "biller.tasks.edit").'
                '.$this->getDeleteButtonAttribute("delete-task", "biller.tasks.destroy").'
                ';
    }
}
