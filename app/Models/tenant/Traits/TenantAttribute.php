<?php

namespace App\Models\tenant\Traits;

use App\Models\customer\Customer;
use App\Models\tenant\Tenant;

trait TenantAttribute
{
    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getViewButtonAttribute("manage-business-account", "biller.tenants.show")
            . ' ' . $this->getEditButtonAttribute("edit-business-account", "biller.tenants.edit")
            . ' ' . $this->getDeleteButtonAttribute("delete-business-account", "biller.tenants.destroy");
    }

    /** 
     * Parent Account
     * */
    public function getParentAccountAttribute()
    {
        if (isset($this->attributes['parent_account_id'])) {
            return Customer::withoutGlobalScopes() 
            ->find($this->attributes['parent_account_id']);
        }
        return;
    }
}