<?php

namespace App\Models\contract\Traits;

use App\Models\contract\PMDocument;
use App\Models\contract_equipment\ContractEquipment;
use App\Models\customer\Customer;
use App\Models\equipment\Equipment;
use App\Models\task_schedule\TaskSchedule;

trait ContractRelationship
{
    public function task_schedules()
    {
        return $this->hasMany(TaskSchedule::class);
    }

    public function contract_equipments() 
    {
        return $this->hasMany(ContractEquipment::class)->whereNull('schedule_id');
    }
    
    public function equipments()
    {
        return $this->hasManyThrough(Equipment::class, ContractEquipment::class, 'contract_id', 'id', 'id', 'equipment_id')
            ->whereNull('contract_equipment.schedule_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function pm_docs()
    {
        return $this->hasMany(PMDocument::class, 'contract_id');
    }
}
