<?php

namespace App\Models\prospect\Traits;

/**
 * Class ProductcategoryAttribute.
 */
trait ProspectAttribute
{
    // Make your attributes functions here
    // Further, see the documentation : https://laravel.com/docs/5.4/eloquent-mutators#defining-an-accessor


    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getViewButtonAttribute("manage-lead", "biller.prospects.show") . ' ' 
            . $this->getEditButtonAttribute("edit-lead", "biller.prospects.edit") . ' ' 
            . $this->getDeleteButtonAttribute("delete-lead", "biller.prospects.destroy");
    }
}
