<?php

namespace App\Models\ticket_category\Traits;

trait TicketCategoryAttribute
{
    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getEditButtonAttribute("manage-client", "biller.ticket_categories.edit")
        .' '.$this->getDeleteButtonAttribute("manage-client", "biller.ticket_categories.destroy");     
    }
}
