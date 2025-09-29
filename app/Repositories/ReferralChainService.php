<?php
// app/Services/ReferralChainService.php

namespace App\Repositories;

use App\Models\promotions\ReferralsPromoCodeReservation;
use Illuminate\Support\Str;

trait ReferralChainService
{
    /**
     * Build the SMS body when creating Tier 2 or Tier 3 referrals.
     *
     * @param  \Illuminate\Database\Eloquent\Model $reservation  The NEW reservation (tier2 or tier3)
     * @param  array $tenantMeta [
     *   'tenant_name'       => 'Acme Ltd',
     *   'tenant_promo_code' => 'ACME10',
     *   'discount_text'     => '10%' // or 'KES 500'
     * ]
     * @return string|null
     */
    public function buildReferralMessage($reservation, array $tenantMeta)
    {
        $parent = isset($reservation->referer_uuid) && $reservation->referer_uuid
            ? ReferralResolver::findParent($reservation->referer_uuid)
            : null;

        if (!$parent) {
            // No parent found — likely a Tier 1 (or bad data). Nothing to message here.
            return null;
        }

        // Is parent a Tier 2 (Referral table) => we are creating Tier 3
        $isTier3 = $this->isReferralReservation($parent);

        if ($isTier3) {
            // parent = tier2 (ReferralsPromoCodeReservation), which itself was referred by tier1
            $tier2 = $parent;
            $tier1 = (isset($tier2->referer_uuid) && $tier2->referer_uuid)
                ? ReferralResolver::findParent($tier2->referer_uuid)
                : null;

            if (!$tier1) {
                // Broken chain; skip
                return null;
            }

            $referrerName = $this->twoNames($this->fullName($tier2));        // direct referrer (tier2)
            $refereeName  = $this->twoNames($this->fullName($reservation));  // the new person (tier3)
            $refereePhone = $this->phoneCC($this->extractPhone($reservation));
            $redeemCode   = $this->extractRedeemCode($reservation);
        } else {
            // parent is tier1 (Customers/ThirdParties) => we are creating tier2
            $tier1 = $parent;

            $referrerName = $this->twoNames($this->fullName($tier1));        // direct referrer (tier1)
            $refereeName  = $this->twoNames($this->fullName($reservation));  // the new person (tier2)
            $refereePhone = $this->phoneCC($this->extractPhone($reservation));
            $redeemCode   = $this->extractRedeemCode($reservation);
        }

        $tenant    = isset($tenantMeta['tenant_name']) ? $tenantMeta['tenant_name'] : 'Your Business';
        $promoCode = isset($tenantMeta['tenant_promo_code']) ? $tenantMeta['tenant_promo_code'] : '';
        $discount  = isset($tenantMeta['discount_text']) ? $tenantMeta['discount_text'] : '';

        $msg = sprintf(
            'From %s : Congratulations, %s has referred you to %s of %s. Please contact them about %s which you are offering at %s off. Their redeemable code is %s.',
            $tenant,
            $referrerName,
            $refereeName,
            $refereePhone,
            $promoCode,
            $discount,
            $redeemCode
        );

        return trim($msg);
    }

    /* ---------------- helpers (Laravel 6 / PHP 7 safe) ---------------- */

    protected function isReferralReservation($model)
    {
        // Change class to your actual model if named differently
        return $model instanceof ReferralsPromoCodeReservation;
    }

    protected function fullName($model)
    {
        // Try common name fields (adjust to your schema)
        $fullname = trim((string) (isset($model->name) ? $model->name : (isset($model->name) ? $model->name : '')));
        
        $name  = trim($fullname);
        if ($name === '') {
            $name = trim((string) (isset($model->name) ? $model->name : (isset($model->name) ? $model->name : '')));
        }
        return $name !== '' ? $name : 'Valued Customer';
    }

    protected function twoNames($name)
    {
        $name = trim((string) $name);
        if ($name === '') return 'Valued Customer';
        $parts = preg_split('/\s+/', $name);
        $clean = array();
        foreach ($parts as $p) { if ($p !== '') $clean[] = $p; }
        if (count($clean) >= 2) return $clean[0] . ' ' . $clean[1];
        return $clean[0];
    }

    protected function extractPhone($model)
    {
        // Adjust to your actual phone field(s)
        if (isset($model->phone) && $model->phone) return $model->phone;
        if (isset($model->msisdn) && $model->msisdn) return $model->msisdn;
        return '';
    }

    /**
     * Normalize to +2547XXXXXXXX (default KE). Replace with your tenant CC if needed.
     */
    protected function phoneCC($raw, $defaultCc = '+254')
    {
        $raw = (string) $raw;
        $digits = preg_replace('/\D+/', '', $raw);

        if (Str::startsWith($digits, '254')) {
            return '+' . $digits; // already has country code, just add '+'
        }
        if (Str::startsWith($digits, '07') || Str::startsWith($digits, '01')) {
            return $defaultCc . substr($digits, 1); // 07xxxxxxx -> +2547xxxxxxx
        }
        if (Str::startsWith($raw, '+')) {
            return $raw; // already in E.164
        }
        return $defaultCc . $digits; // fallback
    }

    protected function extractRedeemCode($model)
    {
        if (isset($model->redeemable_code) && $model->redeemable_code) return $model->redeemable_code;
        if (isset($model->promo_code) && $model->promo_code)   return $model->promo_code;
        if (isset($model->code) && $model->code)               return $model->code;
        return '';
    }
}
