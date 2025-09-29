<?php

namespace App\Models\note\Traits;

/**
 * Class NoteAttribute.
 */
trait NoteAttribute
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
         '.$this->getViewButtonAttribute("manage-note", "biller.notes.show").'
                '.$this->getEditButtonAttribute("edit-note", "biller.notes.edit").'
                '.$this->getDeleteButtonAttribute("delete-note", "biller.notes.destroy",'table').'
                ';
    }
}
