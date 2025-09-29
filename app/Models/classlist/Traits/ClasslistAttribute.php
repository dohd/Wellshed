<?php

namespace App\Models\classlist\Traits;

use App\Models\classlist\Classlist;

trait ClasslistAttribute
{
    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getEditButtonAttribute("edit-account", "biller.classlists.edit")
            .' '. $this->getDeleteButtonAttribute("delete-account", "biller.classlists.destroy");
    }

    public function getParentClassAttribute()
    {
        $parent_id = $this->attributes['parent_id'];
        return Classlist::find($parent_id);
    }
}
