<?php

namespace App\Models\message_template\Traits;

/**
 * Class MessageTemplateAttribute.
 */
trait MessageTemplateAttribute
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
         '.$this->getViewButtonAttribute("manage-sms_send", "biller.message_templates.show").'
                '.$this->getEditButtonAttribute("edit-sms_send", "biller.message_templates.edit").'
                '.$this->getDeleteButtonAttribute("delete-sms_send", "biller.message_templates.destroy").'
                ';
    }
}
