<?php

namespace App\Models\productcategory\Traits;

/**
 * Class ProductcategoryAttribute.
 */
trait ProductcategoryAttribute
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
         '.$this->getViewButtonAttribute("manage-product-category", "biller.productcategories.show").'
                '.$this->getEditButtonAttribute("edit-product-category", "biller.productcategories.edit").'
                '.$this->getDeleteButtonAttribute("delete-product-category", "biller.productcategories.destroy").'
                ';
    }
}
