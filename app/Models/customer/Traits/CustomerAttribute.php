<?php

namespace App\Models\customer\Traits;

use App\Models\invoice_payment\InvoicePayment;
use Carbon\Carbon;

/**
 * Class CustomerAttribute.
 */
trait CustomerAttribute
{
    // Make your attributes functions here
    // Further, see the documentation : https://laravel.com/docs/5.4/eloquent-mutators#defining-an-accessor


    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return '
         '.$this->getViewButtonAttribute("manage-customer", "biller.customers.show").'
                '.$this->getEditButtonAttribute("edit-customer", "biller.customers.edit").'
                '.$this->getDeleteButtonAttribute("delete-customer", "biller.customers.destroy",'table').'
                ';
    }

    /**
     * Unallocated Amount Balance
     */
    public function getUnallocatedAmountAttribute()
    {
        $balance = 0;
        $customerId = @$this->attributes['id'];
        if ($customerId) {
            $balance = InvoicePayment::where('customer_id', $customerId)
            ->whereNull('rel_payment_id')
            ->sum(\DB::raw('amount-allocate_ttl'));
        }
        return $balance;
    }

    /**
     * Tenant Subscription Balance
     */
    public function getSubscriptionBalanceAttribute()
    {
        $inv_totals = $this->invoices()->sum('total');
        $dep_totals = $this->deposits()->sum('amount');
        return round($inv_totals - $dep_totals);
    }

    /**
     * Subscription Due in 3 Days
     */
    public function getIsSubscriptionDueAttribute()
    {
        $last_depo = $this->deposits()->orderBy('id', 'DESC')->first(['next_date']);
        if (@$last_depo->next_date && $this->subscription_balance <= 0) {
            $next_date = Carbon::parse($last_depo->next_date);
            $diff = $next_date->diffDays(Carbon::today());
            if ($diff >= 0 && $diff <= 3) return true;
        }
        return false;
    }
}
