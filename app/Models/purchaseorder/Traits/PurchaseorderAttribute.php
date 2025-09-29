<?php

namespace App\Models\purchaseorder\Traits;

/**
 * Class PurchaseorderAttribute.
 */
trait PurchaseorderAttribute
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
         '.$this->getViewButtonAttribute("manage-purchase", "biller.purchaseorders.show").'
                '.$this->getEditButtonAttribute("edit-purchase", "biller.purchaseorders.edit").'
                '.$this->getDeleteButtonAttribute("delete-purchase", "biller.purchaseorders.destroy").'
                ';
    }
}
