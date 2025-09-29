<?php

namespace App\Models\projectstock\Traits;


trait ProjectStockAttribute
{
    // Make your attributes functions here
    // Further, see the documentation : https://laravel.com/docs/5.4/eloquent-mutators#defining-an-accessor


    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getViewButtonAttribute("manage-project", "biller.projectstock.show") . ' ' 
            . $this->getEditButtonAttribute("edit-project", "biller.projectstock.edit") . ' ' 
            . $this->getDeleteButtonAttribute("delete-project", "biller.projectstock.destroy");
    }
}
