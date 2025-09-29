<?php

namespace App\Models\calllist\Traits;

/**
 * Class ProductcategoryAttribute.
 */
trait CallListAttribute
{
    // Make your attributes functions here
    // Further, see the documentation : https://laravel.com/docs/5.4/eloquent-mutators#defining-an-accessor


    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getViewButtonAttribute("manage-lead", "biller.calllists.show") . ' ' 
            . $this->getEditButtonAttribute("edit-lead", "biller.calllists.edit") . ' ' 
            . $this->getDeleteButtonAttribute("delete-lead", "biller.calllists.destroy");
    }
}
