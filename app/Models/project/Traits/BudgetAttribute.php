<?php

namespace App\Models\project\Traits;

trait BudgetAttribute
{
    public function getActionButtonsAttribute()
    {
        return $this->getViewButtonAttribute("manage-project-", "biller.budgets.show")
         . ' ' . $this->getEditButtonAttribute("edit-project", "biller.budgets.edit")
         . ' ' . $this->getDeleteButtonAttribute("delete-project", "biller.budgets.destroy");
    }
}
