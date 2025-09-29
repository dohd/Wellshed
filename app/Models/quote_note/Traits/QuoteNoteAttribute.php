<?php

namespace App\Models\quote_note\Traits;

/**
 * Class QuoteNoteAttribute.
 */
trait QuoteNoteAttribute
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
         '.$this->getViewButtonAttribute("manage-quote", "biller.quote_notes.show").'
                '.$this->getEditButtonAttribute("edit-quote", "biller.quote_notes.edit").'
                '.$this->getDeleteButtonAttribute("delete-quote", "biller.quote_notes.destroy").'
                ';
    }
}
