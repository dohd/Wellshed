<?php

namespace App\Models\third_party_user\Traits;

/**
 * Class ThirdPartyUserAttribute.
 */
trait ThirdPartyUserAttribute
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
         '.$this->getViewButtonAttribute("manage-department", "biller.third_party_users.show").'
                '.$this->getEditButtonAttribute("edit-department", "biller.third_party_users.edit").'
                '.$this->getDeleteButtonAttribute("delete-department", "biller.third_party_users.destroy").'
                ';
    }
}
