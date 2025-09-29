<?php

namespace App\Models\tenant_service\Traits;

trait TenantServiceAttribute
{
    // Make your attributes functions here
    // Further, see the documentation : https://laravel.com/docs/5.4/eloquent-mutators#defining-an-accessor


    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getViewButtonAttribute("manage-account-service", "biller.tenant_services.show")
            . ' ' . $this->getEditButtonAttribute("edit-account-service", "biller.tenant_services.edit")
            . ' ' . $this->getDeleteButtonAttribute("delete-account-service", "biller.tenant_services.destroy");
    }
}