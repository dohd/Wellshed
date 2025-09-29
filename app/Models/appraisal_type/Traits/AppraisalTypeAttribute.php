<?php

namespace App\Models\appraisal_type\Traits;

/**
 * Class AppraisalTypeAttribute.
 */
trait AppraisalTypeAttribute
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
         '.$this->getViewButtonAttribute("manage-employee-appraisal", "biller.appraisal_types.show").'
                '.$this->getEditButtonAttribute("edit-employee-appraisal", "biller.appraisal_types.edit").'
                '.$this->getDeleteButtonAttribute("delete-employee-appraisal", "biller.appraisal_types.destroy").'
                ';
    }
}
