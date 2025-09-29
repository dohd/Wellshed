<?php

namespace App\Models\project\Traits;

use App\Models\project\Budget;

/**
 * Class ProjectRelationship
 */
trait BudgetSkillsetRelationship
{
    public function budget()
    {
        return $this->belongsTo(Budget::class, 'budget_id');
    }
}
