<?php

namespace App\Models\job_category\Traits;

/**
 * Class InvoiceAttribute.
 */
trait JobCategoryAttribute
{
    // Make your attributes functions here
    // Further, see the documentation : https://laravel.com/docs/5.4/eloquent-mutators#defining-an-accessor


    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getViewButtonAttribute("manage-job-categories", "biller.job-categories.show")
             . ' ' . $this->getEditButtonAttribute("edit-job-categories", "biller.job-categories.edit")
            . ' ' . $this->getDeleteButtonAttribute("delete-job-categories", "biller.job-categories.destroy");
    }
}
