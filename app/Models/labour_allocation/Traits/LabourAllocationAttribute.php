<?php

namespace App\Models\labour_allocation\Traits;

/**
 * Class ProductcategoryAttribute.
 */
trait LabourAllocationAttribute
{
    // Make your attributes functions here
    // Further, see the documentation : https://laravel.com/docs/5.4/eloquent-mutators#defining-an-accessor


    /**
     * Action Button Attribute to show in grid
     * 
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return 
            // $this->getViewButtonAttribute("manage-labour_allocation", "biller.labour_allocations.show") . ' ' . 
            $this->getEditButtonAttribute("edit-labour_allocation", "biller.labour_allocations.edit") . ' ' . 
            $this->getDeleteButtonAttribute("delete-labour_allocation", "biller.labour_allocations.destroy");
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
