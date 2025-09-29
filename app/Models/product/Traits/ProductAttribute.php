<?php

namespace App\Models\product\Traits;

/**
 * Class ProductAttribute.
 */
trait ProductAttribute
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
         '.$this->getViewButtonAttribute("manage-product", "biller.products.show").'
                '.$this->getEditButtonAttribute("edit-product", "biller.products.edit").'
                '.$this->getDeleteButtonAttribute("delete-product", "biller.products.destroy", 'table').'
                ';
    }
}
