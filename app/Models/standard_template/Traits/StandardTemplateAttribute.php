<?php

namespace App\Models\standard_template\Traits;

/**
 * Class StandardTemplateAttribute.
 */
trait StandardTemplateAttribute
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
         '.$this->getViewButtonAttribute("manage-standard_template", "biller.standard_templates.show").'
                '.$this->getEditButtonAttribute("edit-standard_template", "biller.standard_templates.edit").'
                '.$this->getDeleteButtonAttribute("delete-standard_template", "biller.standard_templates.destroy").'
                ';
    }
}
