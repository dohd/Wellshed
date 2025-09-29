<?php

namespace App\Models\rfq_analysis\Traits;

trait RfQAnalysisAttribute
{
    public function getActionButtonsAttribute()
    {
        return '
         '.$this->getViewButtonAttribute("manage-rfq", "biller.rfq_analysis.show").'
                '.$this->getEditButtonAttribute("edit-rfq", "biller.rfq_analysis.edit").'
                '.$this->getDeleteButtonAttribute("delete-rfq", "biller.rfq_analysis.destroy").'
                ';
    }
}
