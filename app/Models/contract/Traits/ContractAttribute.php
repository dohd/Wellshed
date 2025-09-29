<?php

namespace App\Models\contract\Traits;

/**
 * Class ProductcategoryAttribute.
 */
trait ContractAttribute
{
    // Make your attributes functions here
    // Further, see the documentation : https://laravel.com/docs/5.4/eloquent-mutators#defining-an-accessor


    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getViewButtonAttribute("manage-pm-contract", "biller.contracts.show")
            . ' ' . $this->getEditButtonAttribute("edit-pm-contract", "biller.contracts.edit")
            . ' ' . $this->getDeleteButtonAttribute("delete-pm-contract", "biller.contracts.destroy");
    }
}
