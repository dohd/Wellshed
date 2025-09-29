<?php

namespace App\Models\billitem\Traits;

/**
 * Class InvoiceAttribute.
 */
trait BillItemAttribute
{
    // Make your attributes functions here
    // Further, see the documentation : https://laravel.com/docs/5.4/eloquent-mutators#defining-an-accessor


    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return '
         '.$this->getViewButtonAttribute("manage-bill", "biller.purchase_items.show").'
                '.$this->getEditButtonAttribute("edit-bill", "biller.purchase_items.edit").'
                '.$this->getDeleteButtonAttribute("delete-bill", "biller.purchase_items.destroy").'
                ';
    }
}
