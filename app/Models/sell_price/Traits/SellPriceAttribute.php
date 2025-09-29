<?php

namespace App\Models\sell_price\Traits;

/**
 * Class SellPriceAttribute.
 */
trait SellPriceAttribute
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
         '.$this->getViewButtonAttribute("manage-purchase", "biller.sell_prices.show").'
                '.$this->getEditButtonAttribute("edit-purchase", "biller.sell_prices.edit").'
                '.$this->getDeleteButtonAttribute("delete-purchase", "biller.sell_prices.destroy").'
                ';
    }
}
