<?php

namespace App\Models\casual_labourer_remuneration\Traits;

use App\Models\casual\CasualLabourer;
use App\Models\casual_labourer_remuneration\CLRWage;
use App\Models\casual_labourer_remuneration\CLRWageItem;
use App\Models\hrm\Hrm;
use App\Models\labour_allocation\LabourAllocation;
use App\Models\utility_bill\UtilityBill;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait CLRRelationship
{
    public function bill()
    {
        return $this->hasOne(UtilityBill::class, 'casual_remun_id');
    }

    public function clrWages()
    {
        return $this->hasMany(CLRWage::class, 'clr_number');
    }

    public function clrWageItems()
    {
        return $this->hasMany(CLRWageItem::class, 'clr_number');
    }

    public function casualLabourers(): BelongsToMany
    {
        return $this->belongsToMany(CasualLabourer::class, 'clr_wages', 'clr_number', 'casual_labourer_id')
            ->withoutGlobalScopes();
    }

    public function labourAllocations(): BelongsToMany
    {
        return $this->belongsToMany(LabourAllocation::class, 'clr_allocations', 'clr_number', 'labour_allocation_id')
            ->withoutGlobalScopes();
    }

    public function creator(): BelongsTo{

        return $this->belongsTo(Hrm::class, 'created_by', 'id');
    }

    public function updater(): BelongsTo{

        return $this->belongsTo(Hrm::class, 'updated_by', 'id');
    }

    public function approver(): BelongsTo{

        return $this->belongsTo(Hrm::class, 'approved_by', 'id');
    }   
}