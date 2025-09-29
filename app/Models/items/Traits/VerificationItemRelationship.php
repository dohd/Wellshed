<?php

namespace App\Models\items\Traits;

use App\Models\items\QuoteItem;
use App\Models\verification\Verification;

trait VerificationItemRelationship
{
   public function verification()
   {
      return $this->belongsTo(Verification::class, 'parent_id');
   }

   public function quote_item()
   {
      return $this->belongsTo(QuoteItem::class, 'quote_item_id');
   }
}
