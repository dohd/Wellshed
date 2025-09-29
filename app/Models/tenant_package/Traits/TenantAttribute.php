<?php

namespace App\Models\tenant\Traits;

trait TenantAttribute
{
    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getViewButtonAttribute("manage-branch", "biller.tenants.show")
            . ' ' . $this->getEditButtonAttribute("edit-branch", "biller.tenants.edit")
            . ' ' . $this->getDeleteButtonAttribute("delete-branch", "biller.tenants.destroy");
    }
}