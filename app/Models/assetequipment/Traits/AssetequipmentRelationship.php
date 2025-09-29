<?php

namespace App\Models\assetequipment\Traits;

use App\Models\assetequipment\Assetequipment;
use App\Models\customfield\Customfield;
use App\Models\transaction\Transaction;
use App\Models\project\Project;
use App\Models\account\Account;


/**
 * Class AssetequipmentRelationship
 */
trait AssetequipmentRelationship
{
    public function group()
    {
        return $this->hasMany('App\Models\customergroup\CustomerGroupEntry');
    }

    public function primary_group()
    {
        return $this->hasOne('App\Models\customergroup\CustomerGroupEntry')->oldest();
    }

    public function invoices()
    {
        return $this->hasMany('App\Models\invoice\Invoice')->orderBy('id', 'DESC');
    }

    public function amount()
    {
        return $this->hasMany(Transaction::class, 'payer_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
