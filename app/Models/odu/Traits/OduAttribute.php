<?php

namespace App\Models\odu\Traits;

/**
 * Class ProductcategoryAttribute.
 */
trait OduAttribute
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
         '.$this->getViewButtonAttribute("manage-branch", "biller.branches.show").'
                '.$this->getEditButtonAttribute("edit-branch", "biller.branches.edit").'
                '.$this->getDeleteButtonAttribute("delete-branch", "biller.branches.destroy").'
                ';
    }
}
