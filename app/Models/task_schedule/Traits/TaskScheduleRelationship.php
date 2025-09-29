<?php

namespace App\Models\task_schedule\Traits;

use App\Models\contract\Contract;
use App\Models\contract_equipment\ContractEquipment;
use App\Models\contractservice\ContractService;
use App\Models\equipment\Equipment;
use App\Models\items\ContractServiceItem;

trait TaskScheduleRelationship
{    
    public function contract_service_items()
    {
        return $this->hasManyThrough(ContractServiceItem::class, ContractService::class, 'schedule_id', 'contractservice_id');
    }
    
    public function contractservices()
    {
        return $this->hasMany(ContractService::class, 'schedule_id');
    }

    public function contractservice()
    {
        return $this->hasOne(ContractService::class, 'schedule_id');
    }

    public function contract_equipments()
    {
        return $this->hasMany(ContractEquipment::class, 'schedule_id');
    }

    public function equipments()
    {
        return $this->belongsToMany(Equipment::class, 'contract_equipment', 'schedule_id', 'equipment_id');
    }

    public function contract()
    {
        return $this->belongsTo(Contract::class)->withoutGlobalScopes();
    }
}
