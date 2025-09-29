<?php

namespace App\Models\petty_cash\Traits;

use App\Models\casual\CasualLabourer;
use App\Models\hrm\Hrm;
use App\Models\petty_cash\PettyCashApproval;
use App\Models\petty_cash\PettyCashItem;
use App\Models\purchase_requisition\PurchaseRequisition;
use App\Models\third_party_user\ThirdPartyUser;

/**
 * Class PettyCashRelationship
 */
trait PettyCashRelationship
{
     public function pr()
     {
        return $this->belongsTo(PurchaseRequisition::class, 'purchase_requisition');
     }

     public function items()
     {
         return $this->hasMany(PettyCashItem::class, 'petty_cash_id');
     }

     public function approvers()
     {
        return $this->hasMany(PettyCashApproval::class, 'petty_cash_id');
     }

     public function employee()
     {
        return $this->belongsTo(Hrm::class, 'employee_id');
     }
     public function casual_labourer()
     {
        return $this->belongsTo(CasualLabourer::class, 'casual_id');
     }
     public function third_party_user()
     {
        return $this->belongsTo(ThirdPartyUser::class, 'third_party_user_id');
     }
}
