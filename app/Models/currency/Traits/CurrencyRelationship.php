<?php

namespace App\Models\currency\Traits;

use App\Models\account\Account;
use App\Models\customer\Customer;
use App\Models\supplier\Supplier;

/**
 * Class CurrencyRelationship
 */
trait CurrencyRelationship
{
    public function accounts()
    {
        return $this->hasMany(Account::class);
    }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    public function supplier()
    {
        return $this->hasMany(Supplier::class);
    }
}
