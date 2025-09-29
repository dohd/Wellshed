<?php

namespace App\Models\utility_bill\Traits;


trait UtilityBillAttribute
{
    // Make your attributes functions here
    // Further, see the documentation : https://laravel.com/docs/5.4/eloquent-mutators#defining-an-accessor


    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        if (isset($this->attributes['document_type'])) {
            $documentType =  $this->attributes['document_type'];
            $referenceId =  $this->attributes['ref_id'];
            if (!$referenceId && $documentType == 'goods_receive_note') {
                return $this->getViewButtonAttribute("manage-bill", "biller.utility_bills.show") . ' ' 
                    . $this->getEditButtonAttribute("edit-bill", "biller.utility_bills.edit") . ' ' 
                    . $this->getDeleteButtonAttribute("delete-bill", "biller.utility_bills.destroy");
            }
            if ($documentType == 'kra_bill') {
                return $this->getViewButtonAttribute("manage-bill", "biller.utility_bills.show") . ' ' 
                    . $this->getEditButtonAttribute("edit-bill", "biller.utility_bills.edit") . ' ' 
                    . $this->getDeleteButtonAttribute("delete-bill", "biller.utility_bills.destroy");
            }
        }
        
        return $this->getViewButtonAttribute("manage-bill", "biller.utility_bills.show");
    }
}
