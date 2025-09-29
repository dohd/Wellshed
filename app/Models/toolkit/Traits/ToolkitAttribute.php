<?php

namespace App\Models\toolkit\Traits;

/**
 * Class PurchaseorderAttribute.
 */
trait toolkitAttribute
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
         '.$this->getViewButtonAttribute("manage-attendance", "biller.toolkits.show").'
                '.$this->getEditButtonAttribute("edit-attendance", "biller.toolkits.edit").'
                '.$this->getDeleteButtonAttribute("delete-attendance", "biller.toolkits.destroy").'
                ';
    }
}
