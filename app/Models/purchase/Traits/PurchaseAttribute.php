<?php

namespace App\Models\purchase\Traits;

/**
 * Class PurchaseorderAttribute.
 */
trait PurchaseAttribute
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
         '.$this->getViewButtonAttribute("manage-purchase", "biller.purchases.show").'
                '.$this->getEditButtonAttribute("edit-purchase", "biller.purchases.edit").'
                '.$this->getDeleteButtonAttribute("delete-purchase", "biller.purchases.destroy").'
                ';
    }
}
