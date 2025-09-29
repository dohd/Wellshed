<?php

namespace App\Models\account\Traits;

use App\Models\account\Account;

/**
 * Class AccountAttribute.
 */
trait AccountAttribute
{
    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return '
         '.$this->getViewButtonAttribute("manage-account", "biller.accounts.show").'
                '.$this->getEditButtonAttribute("edit-account", "biller.accounts.edit").'
                '.$this->getDeleteButtonAttribute("delete-account", "biller.accounts.destroy").'
                ';
    }

    public function getHasSubAccountsAttribute()
    {
        $hasSubAccounts = false;
        if (!isset($this->attributes['parent_id']) && isset($this->attributes['id'])) {
            $hasSubAccounts = Account::where('parent_id', $this->attributes['id'])->exists();
            return $hasSubAccounts;
        }
        return $hasSubAccounts;
    }
}
