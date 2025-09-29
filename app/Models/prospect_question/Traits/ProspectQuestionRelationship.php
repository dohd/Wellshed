<?php

namespace App\Models\prospect_question\Traits;

use App\Models\prospect_question\ProspectQuestionItem;

/**
 * Class ProspectQuestionRelationship
 */
trait ProspectQuestionRelationship
{
    public function questions()
    {
        return $this->hasMany(ProspectQuestionItem::class);
    }
}
