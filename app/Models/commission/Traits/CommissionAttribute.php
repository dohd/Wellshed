<?php

namespace App\Models\commission\Traits;

/**
 * Class CommissionAttribute.
 */
trait CommissionAttribute
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
         '.$this->getViewButtonAttribute("manage-commission", "biller.commissions.show").'
                '.$this->getEditButtonAttribute("edit-commission", "biller.commissions.edit").'
                '.$this->getDeleteButtonAttribute("delete-commission", "biller.commissions.destroy").'
                ';
    }
}
