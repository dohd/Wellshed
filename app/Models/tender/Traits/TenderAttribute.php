<?php

namespace App\Models\tender\Traits;

/**
 * Class TenderAttribute.
 */
trait TenderAttribute
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
         '.$this->getViewButtonAttribute("manage-tender", "biller.tenders.show").'
                '.$this->getEditButtonAttribute("edit-tender", "biller.tenders.edit").'
                '.$this->getDeleteButtonAttribute("delete-tender", "biller.tenders.destroy").'
                ';
    }
}
