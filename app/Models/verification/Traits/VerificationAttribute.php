<?php

namespace App\Models\verification\Traits;

trait VerificationAttribute
{
    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getViewButtonAttribute("manage-quote", "biller.verifications.show")
        .' '. $this->getEditButtonAttribute("edit-quote", "biller.verifications.edit")
        .' '.$this->getDeleteButtonAttribute("delete-quote", "biller.verifications.destroy");     
    }

    public function getNextTidAttribute()
    {
        return $this->query()->where('ins', auth()->user()->ins)->max('tid') + 1;
    }
}
