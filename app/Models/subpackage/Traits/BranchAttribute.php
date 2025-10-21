<?php

namespace App\Models\subpackage\Traits;

/**
 * Class ProductcategoryAttribute.
 */
trait BranchAttribute
{
    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getViewButtonAttribute("manage-branch", "biller.subpackages.show")
            . ' ' . $this->getEditButtonAttribute("edit-branch", "biller.subpackages.edit")
            . ' ' . $this->getDeleteButtonAttribute("delete-branch", "biller.branches.destroy");
    }
}