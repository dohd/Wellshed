<?php

namespace App\Models\sms_response\Traits;

/**
 * Class SmsResponseAttribute.
 */
trait SmsResponseAttribute
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
         '.$this->getViewButtonAttribute("manage-sms_response", "biller.sms_responses.show").'
                '.$this->getEditButtonAttribute("edit-sms_response", "biller.sms_responses.edit").'
                '.$this->getDeleteButtonAttribute("delete-sms_response", "biller.sms_responses.destroy").'
                ';
    }
}
