<?php

namespace App\Models\project\Traits;

use App\Models\event\Event;
use App\Models\event\EventRelation;
use App\Models\hrm\Hrm;
use App\Models\misc\Misc;
use App\Models\project\Project;
use App\Models\project\ProjectMileStone;
use App\Models\project\ProjectRelations;
use App\Models\project\TaskRelations;

/**
 * Class TaskRelationship
 */
trait TaskRelationship
{
    public function milestone()
    {
        return $this->hasOneThrough(ProjectMileStone::class, ProjectRelations::class, 'task_id', 'id', 'id', 'milestone_id');
    }

    public function tags()
    {
        return $this->hasManyThrough(Misc::class, ProjectRelations::class, 'task_id', 'id', 'id', 'misc_id');
    }

    public function events()
    {
        return $this->hasOneThrough(Event::class, EventRelation::class, 'r_id', 'id', 'id', 'event_id')->where('related', '=', 2)->withoutGlobalScopes();
    }

    public function task_status()
    {
        return $this->hasOne(Misc::class, 'id', 'status')->where('section', '=', 2);
    }

    public function users()
    {
        return $this->hasManyThrough(Hrm::class, ProjectRelations::class, 'task_id', 'id', 'id', 'user_id');
    }

    public function creator()
    {
        return $this->hasOne(Hrm::class, 'id', 'creator_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function task_relations()
    {
        return $this->hasMany(TaskRelations::class, 'todolist_id');
    }
}
