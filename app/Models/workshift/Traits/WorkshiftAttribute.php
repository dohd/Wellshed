<?php

namespace App\Models\workshift\Traits;

/**
 * Class PurchaseorderAttribute.
 */
trait WorkshiftAttribute
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
         '.$this->getViewButtonAttribute("manage-attendance", "biller.workshifts.show").'
                '.$this->getEditButtonAttribute("edit-attendance", "biller.workshifts.edit").'
                '.$this->getDeleteButtonAttribute("delete-attendance", "biller.workshifts.destroy").'
                ';
    }
}
