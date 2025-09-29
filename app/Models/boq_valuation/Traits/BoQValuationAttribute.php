<?php

namespace App\Models\boq_valuation\Traits;

/**
 * Class BoQValuationAttribute.
 */
trait BoQValuationAttribute
{
    // Make your attributes functions here
    // Further, see the documentation : https://laravel.com/docs/5.4/eloquent-mutators#defining-an-accessor


    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getViewButtonAttribute("manage-opening-stock", "biller.boq_valuations.show")
        // .' '. $this->getEditButtonAttribute("edit-opening-stock", "biller.boq_valuations.edit")
        .' '.$this->getDeleteButtonAttribute("delete-opening-stock", "biller.boq_valuations.destroy"); 
    }
}
