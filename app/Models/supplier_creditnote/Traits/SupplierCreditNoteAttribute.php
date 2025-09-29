<?php

namespace App\Models\supplier_creditnote\Traits;

/**
 * Class SupplierCreditNoteAttribute.
 */
trait SupplierCreditNoteAttribute
{
    // Make your attributes functions here
    // Further, see the documentation : https://laravel.com/docs/5.4/eloquent-mutators#defining-an-accessor


    /**
     * Action Button Attribute to show in grid
     * @return string
     */
     public function getActionButtonsAttribute()
    {
        return 
            $this->getViewButtonAttribute("manage-credit-note", "biller.supplier_creditnotes.show")
            .' '.$this->getEditButtonAttribute("edit-credit-note", "biller.supplier_creditnotes.edit")
            .' '.$this->getDeleteButtonAttribute("delete-credit-note", "biller.supplier_creditnotes.destroy");
    }

    /**
     * Check if memo is of foreign currency
     */
    public function getIsFxAttribute()
    {
        $fxRate = round($this->attributes['fx_curr_rate'], 2);
        if (!$fxRate == 0 && !$fxRate == 1) return true; 
        return false;
    }
}
