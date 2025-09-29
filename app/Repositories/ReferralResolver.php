<?php
// app/Support/ReferralResolver.php

namespace App\Repositories;

use App\Models\promotions\CustomersPromoCodeReservation;
use App\Models\promotions\ReferralsPromoCodeReservation;
use App\Models\promotions\ThirdPartiesPromoCodeReservation;

class ReferralResolver
{
    /**
     * Find the parent reservation (tier1 or tier2) whatever table it lives in.
     * @param  string|null $refererUuid
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public static function findParent($refererUuid)
    {
        if (!$refererUuid) return null;

        return ThirdPartiesPromoCodeReservation::withoutGlobalScopes()->find($refererUuid)
            ?: CustomersPromoCodeReservation::withoutGlobalScopes()->find($refererUuid)
            ?: ReferralsPromoCodeReservation::withoutGlobalScopes()->find($refererUuid);
    }
}
