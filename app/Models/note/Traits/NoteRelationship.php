<?php

namespace App\Models\note\Traits;

use App\Models\hrm\Hrm;
use App\Models\project\Project;
use App\Models\project\ProjectRelations;

/**
 * Class NoteRelationship
 */
trait NoteRelationship
{
    public function project()
    {
        return $this->hasOne(ProjectRelations::class, 'note_id', 'id');
    }

        public function creator()
    {
        return $this->belongsTo(Hrm::class, 'creator_id', 'id');
    }

    public function proj()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
}
