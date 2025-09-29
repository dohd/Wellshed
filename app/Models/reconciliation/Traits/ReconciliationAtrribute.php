<?php

namespace App\Models\reconciliation\Traits;

trait ReconciliationAtrribute
{
    // Make your attributes functions here
    // Further, see the documentation : https://laravel.com/docs/5.4/eloquent-mutators#defining-an-accessor


    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getViewButtonAttribute("manage-project", "biller.reconciliations.show")
        . ' ' . $this->getEditButtonAttribute("manage-project", "biller.reconciliations.edit")
            . ' ' . $this->getDeleteButtonAttribute("manage-project", "biller.reconciliations.destroy");
    }
}
