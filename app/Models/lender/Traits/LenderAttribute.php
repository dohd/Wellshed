<?php

namespace App\Models\lender\Traits;

/**
 * Class LenderAttribute.
 */
trait LenderAttribute
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
         '.$this->getViewButtonAttribute("manage-customer", "biller.lenders.show").'
                '.$this->getEditButtonAttribute("edit-customer", "biller.lenders.edit").'
                '.$this->getDeleteButtonAttribute("delete-customer", "biller.lenders.destroy",'table').'
                ';
    }
}
