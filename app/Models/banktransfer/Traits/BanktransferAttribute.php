<?php

namespace App\Models\banktransfer\Traits;

use App\Models\banktransfer\Banktransfer;

/**
 * Class ProductcategoryAttribute.
 */
trait BanktransferAttribute
{
    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getViewButtonAttribute("manage-money-transfer", "biller.banktransfers.show") 
            . ' ' . $this->getEditButtonAttribute("edit-money-transfer", "biller.banktransfers.edit")
            . ' ' . $this->getDeleteButtonAttribute("delete-money-transfer", "biller.banktransfers.destroy");                
    }

    function getTransToAttribute() 
    {
        return Banktransfer::where('tid', $this->tid)->where('debit', '>', 0)->first();
    }

    function getTransFromAttribute() 
    {
        return Banktransfer::where('tid', $this->tid)->where('credit', '>', 0)->first();
    }
}
