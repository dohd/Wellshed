<?php

namespace App\Models\petty_cash\Traits;

/**
 * Class PettyCashAttribute.
 */
trait PettyCashAttribute
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
         '.$this->getViewButtonAttribute("manage-petty_cash", "biller.petty_cashs.show").'
                '.$this->getEditButtonAttribute("edit-petty_cash", "biller.petty_cashs.edit").'
                '.$this->getDeleteButtonAttribute("delete-petty_cash", "biller.petty_cashs.destroy").'
                ';
    }
}
