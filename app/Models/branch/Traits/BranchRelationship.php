<?php

namespace App\Models\branch\Traits;

use App\Models\contract_equipment\ContractEquipment;
use App\Models\contractservice\ContractService;
use App\Models\customer\Customer;
use App\Models\equipment\Equipment;
use App\Models\items\ContractServiceItem;
use App\Models\lead\Lead;

/**
 * Class ProductcategoryRelationship
 */
trait BranchRelationship
{
    public function contract_equipments()
    {
        return $this->hasManyThrough(ContractEquipment::class, Equipment::class, 'branch_id', 'equipment_id')->whereNull('contract_id');
    }

    public function taskschedule_equipments()
    {
        return $this->hasManyThrough(ContractEquipment::class, Equipment::class, 'branch_id', 'equipment_id')->whereNotNull('schedule_id');
    }

    public function service_contract_items()
    {
        return $this->hasManyThrough(ContractServiceItem::class, Equipment::class, 'branch_id', 'equipment_id');
    }

    public function contract_services()
    {
        return $this->hasMany(ContractService::class);
    }

    public function equipments()
    {
        return $this->hasMany(Equipment::class);
    }

    public function leads()
    {
        return $this->hasMany(Lead::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
