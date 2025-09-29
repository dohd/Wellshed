<?php

namespace App\Models\withholding\Traits;

trait WithholdingAttribute
{
    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getViewButtonAttribute("manage-withholding-cert", "biller.withholdings.show")
             .' '.$this->getEditButtonAttribute("edit-withholding-cert*", "biller.withholdings.edit")
             .' '.$this->getDeleteButtonAttribute("delete-withholding-cert", "biller.withholdings.destroy");
    }
}
