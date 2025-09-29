<?php

namespace App\Models\lead\Traits;

use App\Models\lead\Lead;
use App\Models\lead\OmniChat;
use App\Models\lead\OmniFeedback;

trait AgentLeadRelationship
{
     public function lead()
     {
          return $this->hasOne(Lead::class);
     }

     public function omniChat()
     {
          return $this->hasOneThrough(OmniChat::class, OmniFeedback::class, 'agent_lead_id', 'id', 'id', 'omni_chat_id')
               ->withoutGlobalScopes();
     }
}
