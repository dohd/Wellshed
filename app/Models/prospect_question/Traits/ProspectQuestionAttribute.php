<?php

namespace App\Models\prospect_question\Traits;

/**
 * Class ProspectQuestionAttribute.
 */
trait ProspectQuestionAttribute
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
         '.$this->getViewButtonAttribute("manage-lead", "biller.prospect_questions.show").'
                '.$this->getEditButtonAttribute("edit-lead", "biller.prospect_questions.edit").'
                '.$this->getDeleteButtonAttribute("delete-lead", "biller.prospect_questions.destroy").'
                ';
    }
}
