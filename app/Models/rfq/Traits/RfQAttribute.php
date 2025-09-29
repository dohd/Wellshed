<?php

namespace App\Models\rfq\Traits;

trait RfQAttribute
{
    public function getActionButtonsAttribute()
    {
        return '
         '.$this->getViewButtonAttribute("manage-rfq", "biller.rfq.show").'
                '.$this->getEditButtonAttribute("edit-rfq", "biller.rfq.edit").'
                '.$this->getDeleteButtonAttribute("delete-rfq", "biller.rfq.destroy").'
                ';
    }
}
