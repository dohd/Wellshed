<?php

namespace App\Models\projectstocktransfer\Traits;

/**
 * Class PurchaseorderAttribute.
 */
trait ProjectstocktransferAttribute
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
         '.$this->getViewButtonAttribute("manage-stock-transfer", "biller.projectstocktransfers.show").'
                '.$this->getEditButtonAttribute("edit-stock-transfer", "biller.projectstocktransfers.edit").'
                '.$this->getDeleteButtonAttribute("delete-stock-transfer", "biller.projectstocktransfers.destroy",'table').'
                ';
    }
}
