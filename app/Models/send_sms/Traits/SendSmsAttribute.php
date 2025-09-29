<?php

namespace App\Models\send_sms\Traits;

/**
 * Class SendSmsAttribute.
 */
trait SendSmsAttribute
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
         '.$this->getViewButtonAttribute("manage-sms_send", "biller.send_sms.show").'
                '.$this->getEditButtonAttribute("edit-sms_send", "biller.send_sms.edit").'
                '.$this->getDeleteButtonAttribute("delete-sms_send", "biller.send_sms.destroy").'
                ';
    }
}
