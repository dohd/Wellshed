<?php

namespace App\Models\advance_payment\Traits;

trait AdvancePaymentAttribute
{
    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getViewButtonAttribute("manage-advance-payment", "biller.advance_payments.show")
        .' '. $this->getEditButtonAttribute("edit-advance-payment", "biller.advance_payments.edit")
        .' '.$this->getDeleteButtonAttribute("delete-advance-payment", "biller.advance_payments.destroy");     
    }
}
