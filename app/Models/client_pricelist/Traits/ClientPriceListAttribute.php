<?php

namespace App\Models\client_pricelist\Traits;

/**
 * Class WarehouseAttribute.
 */
trait ClientPriceListAttribute
{
    // Make your attributes functions here
    // Further, see the documentation : https://laravel.com/docs/5.4/eloquent-mutators#defining-an-accessor


    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return  
        // $this->getViewButtonAttribute("manage-product", "biller.pricegroups.show") 
        //  . ' ' . $this->getEditButtonAttribute("product-create", "biller.pricegroups.edit") 
        $this->getDeleteButtonAttribute("delete-pricelist", "biller.client_products.destroy") ;
    }
}
