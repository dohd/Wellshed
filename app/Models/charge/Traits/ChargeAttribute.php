<?php

namespace App\Models\charge\Traits;

/**
 * Trait ChargeAttribute.
 */
trait ChargeAttribute
{
    // Make your attributes functions here
    // Further, see the documentation : https://laravel.com/docs/5.4/eloquent-mutators#defining-an-accessor


    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getViewButtonAttribute("manage-account-charge*", "biller.charges.show")
            . ' ' . $this->getEditButtonAttribute("edit-account-charge*", "biller.charges.edit")
            . ' ' . $this->getDeleteButtonAttribute("delete-account-charge", "biller.charges.destroy");
    }
}
