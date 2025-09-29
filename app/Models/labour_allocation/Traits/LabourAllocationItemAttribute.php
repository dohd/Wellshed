<?php

namespace App\Models\labour_allocation\Traits;

/**
 * Class ProductcategoryAttribute.
 */
trait LabourAllocationItemAttribute
{
    // Make your attributes functions here
    // Further, see the documentation : https://laravel.com/docs/5.4/eloquent-mutators#defining-an-accessor


    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        // return $this->getViewButtonAttribute("manage-project", "biller.labour_allocations.show") . ' ' 
        //     . $this->getEditButtonAttribute("edit-project", "biller.labour_allocations.edit") . ' ' 
        //     . $this->getDeleteButtonAttribute("delete-project", "biller.labour_allocations.destroy");
    }
    
    /**
     * Labour Hour Atrribute
     * 
     * @return float
     */
    public function getHrsAttribute($value)
    {
        return +$value;
    }
}
