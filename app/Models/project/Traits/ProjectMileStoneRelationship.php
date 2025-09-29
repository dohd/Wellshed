<?php

namespace App\Models\project\Traits;

use App\Models\hrm\Hrm;
use App\Models\items\PurchaseItem;
use App\Models\project\MileStoneItem;
use App\Models\project\Project;
use App\Models\project\ProjectRelations;
use App\Models\project\Task;
use App\Models\purchase\Purchase;
use App\Models\purchase_request\PurchaseRequest;

/**
 * Class ProjectRelationship
 */
trait ProjectMileStoneRelationship
{
    public function purchases()
    {
        return $this->hasMany(Purchase::class, 'project_milestone');
    }

    public function purchase_items()
    {
        return $this->hasMany(PurchaseItem::class, 'budget_line_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'milestone_id');
    }

    public function creator()
    {
        return $this->belongsTo(Hrm::class, 'user_id', 'id')->withoutGlobalScopes();
    }
    public function items()
    {
        return $this->hasMany(MileStoneItem::class,'milestone_id');
    }
    public function requisitions()
    {
        return $this->hasMany(PurchaseRequest::class,'project_milestone_id');
    }
    public function users()
    {
        return $this->hasManyThrough(Hrm::class, ProjectRelations::class, 'milestone_id', 'id', 'id', 'user_id')->whereNull('task_id');
    }
}
