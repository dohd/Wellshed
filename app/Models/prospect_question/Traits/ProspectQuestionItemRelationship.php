<?php

namespace App\Models\prospect_question\Traits;

use App\Models\prospect_question\ProspectQuestion;

/**
 * Class ProspectQuestionRelationship
 */
trait ProspectQuestionItemRelationship
{
    public function prospect_question()
    {
    return $this->belongsTo(ProspectQuestion::class, 'prospect_question_id');
    }
}
