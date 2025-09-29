<?php

namespace App\Models\billpayment\Traits;


trait BillpaymentAtrribute
{
    // Make your attributes functions here
    // Further, see the documentation : https://laravel.com/docs/5.4/eloquent-mutators#defining-an-accessor


    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getViewButtonAttribute("manage-bill", "biller.billpayments.show") . ' ' 
            . $this->getEditButtonAttribute("edit-bill", "biller.billpayments.edit") . ' ' 
            . $this->getDeleteButtonAttribute("delete-bill", "biller.billpayments.destroy");
    }
}
