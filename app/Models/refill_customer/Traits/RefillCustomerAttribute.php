<?php

namespace App\Models\refill_customer\Traits;

trait RefillCustomerAttribute
{
    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getViewButtonAttribute("manage-refill-customer", "biller.refill_customers.show")
        .' '. $this->getEditButtonAttribute("edit-refill-customer", "biller.refill_customers.edit")
        .' '.$this->getDeleteButtonAttribute("delete-refill-customer", "biller.refill_customers.destroy");     
    }
}
