<?php

namespace App\Models\tenant_ticket\Traits;

trait TenantTicketAttribute
{
    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getViewButtonAttribute("manage-client-area-ticket", "biller.tenant_tickets.show")
            . ' ' . $this->getEditButtonAttribute("manage-client-area-ticket", "biller.tenant_tickets.edit")
            . ' ' . $this->getDeleteButtonAttribute("manage-client-area-ticket", "biller.tenant_tickets.destroy");
    }
}