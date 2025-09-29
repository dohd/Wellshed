<?php

namespace App\Models\remark\Traits;

/**
 * Class ProductcategoryAttribute.
 */
trait RemarkAttribute
{
    // Make your attributes functions here
    // Further, see the documentation : https://laravel.com/docs/5.4/eloquent-mutators#defining-an-accessor


    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getViewButtonAttribute("manage-remark", "biller.remarks.show") . ' ' 
            . $this->getEditButtonAttribute("edit-remark", "biller.remarks.edit") . ' ' 
            . $this->getDeleteButtonAttribute("delete-remark", "biller.remarks.destroy");
    }
}
