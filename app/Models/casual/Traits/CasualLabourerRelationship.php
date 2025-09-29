<?php

namespace App\Models\casual\Traits;

use App\Models\casual\CasualDoc;
use App\Models\casual_labourer_remuneration\CLRWage;
use App\Models\casual_labourer_remuneration\CLRWageItem;
use App\Models\job_category\JobCategory;
use App\Models\labour_allocation\CasualWeeklyHr;
use App\Models\labour_allocation\LabourAllocation;
use App\Models\wage_item\WageItem;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Class CasualLabourerRelationship
 */
trait CasualLabourerRelationship
{
    public function casualWeeklyHrs() {
        return $this->hasMany(CasualWeeklyHr::class, 'casual_labourer_id');
    }

    public function clrWages() {
        return $this->hasMany(CLRWage::class, 'casual_labourer_id');
    }

    public function clrWageItems() {
        return $this->hasMany(CLRWageItem::class, 'casual_labourer_id');
    }

    public function wageItems() {
        return $this->belongsToMany(WageItem::class, 'casual_wage_item', 'casual_id');
    }

    public function job_category() {
        return $this->belongsTo(JobCategory::class, 'job_category_id');
    }

    public function labourAllocations(): BelongsToMany
    {
        return $this->belongsToMany(LabourAllocation::class, 'casual_labourers_allocations', 'casual_labourer_id', 'labour_allocation_id');
    }

    public function casual_docs()
    {
        return $this->hasMany(CasualDoc::class, 'casual_labourer_id');
    }

}
