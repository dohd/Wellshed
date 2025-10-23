<?php

namespace App\Models\mpesa\Traits;

/**
 * Class MpesaConfigAttribute.
 */
trait MpesaConfigAttribute
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
         '.$this->getViewButtonAttribute("manage-department", "biller.mpesa_configs.show").'
                '.$this->getEditButtonAttribute("edit-department", "biller.mpesa_configs.edit").'
                '.$this->getDeleteButtonAttribute("delete-department", "biller.mpesa_configs.destroy",'table').'
                ';
    }
}
