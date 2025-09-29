<?php

namespace App\Models\account\Traits;

use App\Models\account\Account;

trait AccountTypeRelationship
{
    public function accounts()
    {
        return $this->hasMany(Account::class);
    }
}