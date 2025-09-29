<?php

namespace App\Models\rjc\Traits;

use App\Models\items\RjcItem;
use App\Models\project\Project;

trait RjcRelationship
{
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function rjc_items()
    {
        return $this->hasMany(RjcItem::class)->withoutGlobalScopes();
    }
}
