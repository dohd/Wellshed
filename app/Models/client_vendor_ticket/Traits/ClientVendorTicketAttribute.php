<?php

namespace App\Models\client_vendor_ticket\Traits;

trait ClientVendorTicketAttribute
{
    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getViewButtonAttribute("manage-crm-ticket", "biller.client_vendor_tickets.show")
            . ' ' . $this->getEditButtonAttribute("edit-crm-ticket", "biller.client_vendor_tickets.edit")
            . ' ' . $this->getDeleteButtonAttribute("delete-crm-ticket", "biller.client_vendor_tickets.destroy");
    }
}