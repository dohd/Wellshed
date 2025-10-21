<?php

namespace App\Models\subscription\Traits;

/**
 * Class CustomerAttribute.
 */
trait CustomerAttribute
{
    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return 
            $this->getViewButtonAttribute("manage-branch", "biller.subscriptions.show")
            .' '. $this->getEditButtonAttribute("edit-branch", "biller.subscriptions.edit")
            .' '. $this->getDeleteButtonAttribute("delete-branch", "biller.subscriptions.destroy");
    }
}
