<?php

namespace App\Models\contractservice\Traits;

/**
 * Class ProductcategoryAttribute.
 */
trait ContractServiceAtrribute
{
    // Make your attributes functions here
    // Further, see the documentation : https://laravel.com/docs/5.4/eloquent-mutators#defining-an-accessor


    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getViewButtonAttribute("manage-project", "biller.contractservices.show") 
        . ' ' . $this->getEditButtonAttribute("edit-project", "biller.contractservices.edit")
        . ' ' . $this->getDeleteButtonAttribute("delete-project", "biller.contractservices.destroy");
    }
}
