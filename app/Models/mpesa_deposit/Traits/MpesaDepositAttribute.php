<?php

namespace App\Models\mpesa_deposit\Traits;

trait MpesaDepositAttribute
{
    /**
     * Action Button Attribute to show in grid
     * 
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getViewButtonAttribute("manage-lead", "biller.mpesa_deposits.show");
    }

    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->middle_name} {$this->last_name}";
    }
}
