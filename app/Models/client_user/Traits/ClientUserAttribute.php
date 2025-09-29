<?php

namespace App\Models\client_user\Traits;

trait ClientUserAttribute
{
    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getViewButtonAttribute("manage-crm-user", "biller.client_users.show")
        . ' ' . $this->getEditButtonAttribute("edit-crm-user", "biller.client_users.edit")
        . ' ' . $this->getDeleteButtonAttribute("delete-crm-user", "biller.client_users.destroy");
    }
}