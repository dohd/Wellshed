<?php

namespace App\Models\bill\Traits;

/**
 * Class InvoiceAttribute.
 */
trait BillAttribute
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
         '.$this->getViewButtonAttribute("manage-bill", "biller.purchases.show").'
                '.$this->getEditButtonAttribute("edit-bill", "biller.purchases.edit").'
                '.$this->getDeleteButtonAttribute("delete-bill", "biller.purchases.destroy").'
                ';
    }
}
