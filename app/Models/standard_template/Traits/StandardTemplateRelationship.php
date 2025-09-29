<?php

namespace App\Models\standard_template\Traits;

use App\Models\standard_template\StandardTemplateItem;

/**
 * Class StandardTemplateRelationship
 */
trait StandardTemplateRelationship
{
    public function standard_template_items()
    {
        return $this->hasMany(StandardTemplateItem::class);
    }
}
