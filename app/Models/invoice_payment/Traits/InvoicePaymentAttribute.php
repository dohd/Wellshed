<?php

namespace App\Models\invoice_payment\Traits;

use App\Models\invoice_payment\InvoicePayment;

/**
 * Class InvoiceAttribute.
 */
trait InvoicePaymentAttribute
{
    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getViewButtonAttribute("manage-invoice", "biller.invoice_payments.show")
            . ' ' . $this->getEditButtonAttribute("edit-invoice", "biller.invoice_payments.edit") 
            . ' ' . $this->getDeleteButtonAttribute("delete-invoice", "biller.invoice_payments.destroy");
    }

    /**
     * Check if payment is of foreign currency
     */
    public function getIsFxAttribute()
    {
        $fxRate = round($this->attributes['fx_curr_rate'], 2);
        if (!$fxRate == 0 && !$fxRate == 1) return true; 
        return false;
    }
}
