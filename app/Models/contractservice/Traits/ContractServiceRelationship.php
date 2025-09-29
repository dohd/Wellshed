<?php

namespace App\Models\contractservice\Traits;

use App\Models\branch\Branch;
use App\Models\contract\Contract;
use App\Models\customer\Customer;
use App\Models\items\ContractServiceItem;
use App\Models\task_schedule\TaskSchedule;

trait ContractServiceRelationship
{
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function task_schedule()
    {
        return $this->belongsTo(TaskSchedule::class, 'schedule_id');
    }

    public function items()
    {
        return $this->hasMany(ContractServiceItem::class, 'contractservice_id');
    }
}
