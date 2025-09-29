<?php

namespace App\Models\labour_allocation\Traits;

use App\Models\casual\CasualLabourer;
use App\Models\casual_labourer_remuneration\CasualLabourersRemuneration;
use App\Models\casual_labourer_remuneration\CLRAllocation;
use App\Models\Company\Company;
use App\Models\hrm\Hrm;
use App\Models\labour_allocation\CasualWeeklyHr;
use App\Models\labour_allocation\LabourAllocationItem;
use App\Models\project\Project;
use App\Models\project\ProjectMileStone;
use App\Models\project\Task;
use App\Models\project\TaskRelations;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class ProductcategoryRelationship
 */
trait LabourAllocationRelationship
{
    public function company()
    {
        return $this->belongsTo(Company::class, 'ins');
    }  

    public function projectMilestone()
    {
        return $this->belongsTo(ProjectMileStone::class, 'project_milestone');
    }    

    public function casualWeeklyHrs()
    {
        return $this->hasMany(CasualWeeklyHr::class, 'labour_allocation_id');
    }

    public function employee()
    {
        return $this->belongsTo(Hrm::class, 'employee_id');
    }

    public function items()
    {
        return $this->hasMany(LabourAllocationItem::class, 'labour_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function casualLabourers(): BelongsToMany
    {
        return $this->belongsToMany(CasualLabourer::class, 'casual_labourers_allocations', 'labour_allocation_id', 'casual_labourer_id');
    }

    public function casualLabourersRemuneration()
    {
        return $this->hasOneThrough(CasualLabourersRemuneration::class, CLRAllocation::class, 'labour_allocation_id', 'clr_number', 'id', 'clr_number');
    }

    public function clrPivot(): HasMany
    {
        return $this->hasMany(CLRAllocation::class, 'labour_allocation_id', 'id');
    }


    public function budgetLine(): BelongsTo
    {
        return $this->belongsTo(ProjectMileStone::class, 'project_milestone', 'id');
    }

    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id', 'id');
    }

    public function task_item()
    {
        return $this->hasOne(TaskRelations::class, 'labour_id');
    }
}
