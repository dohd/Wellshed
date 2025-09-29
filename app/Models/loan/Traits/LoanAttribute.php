<?php

namespace App\Models\loan\Traits;

trait LoanAttribute
{
    // Make your attributes functions here
    // Further, see the documentation : https://laravel.com/docs/5.4/eloquent-mutators#defining-an-accessor


    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getViewButtonAttribute("manage-loan", "biller.loans.show") . ' ' 
            // . $this->getEditButtonAttribute("transaction-manage", "biller.loans.edit") . ' ' 
            . $this->getDeleteButtonAttribute("delete-loan", "biller.loans.destroy");
    }
}
