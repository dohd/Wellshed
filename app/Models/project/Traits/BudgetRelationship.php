<?php

namespace App\Models\project\Traits;

use App\Models\project\BudgetItem;
use App\Models\project\BudgetSkillset;
use App\Models\quote\Quote;

/**
 * Class ProjectRelationship
 */
trait BudgetRelationship
{
    public function quote()
    {
        return $this->belongsTo(Quote::class, 'quote_id');
    }

    public function items()
    {
        return $this->hasMany(BudgetItem::class);
    }
    
    public function skillsets()
    {
        return $this->hasMany(BudgetSkillset::class);
    }
}
