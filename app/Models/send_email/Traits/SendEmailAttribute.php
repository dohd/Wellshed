<?php

namespace App\Models\send_email\Traits;

/**
 * Class SendEmailAttribute.
 */
trait SendEmailAttribute
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
         '.$this->getViewButtonAttribute("manage-send_email", "biller.send_emails.show").'
                '.$this->getEditButtonAttribute("edit-send_email", "biller.send_emails.edit").'
                '.$this->getDeleteButtonAttribute("delete-send_email", "biller.send_emails.destroy").'
                ';
    }
}
