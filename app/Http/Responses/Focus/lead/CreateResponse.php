<?php

namespace App\Http\Responses\Focus\lead;

use App\Models\account\Account;
use App\Models\additional\Additional;
use App\Models\branch\Branch;
use App\Models\classlist\Classlist;
use App\Models\currency\Currency;
use App\Models\customer\Customer;
use App\Models\lead\AgentLead;
use App\Models\lead\Lead;
use App\Models\lead\LeadSource;
use App\Models\promotions\CustomersPromoCodeReservation;
use App\Models\promotions\ReferralsPromoCodeReservation;
use App\Models\promotions\ThirdPartiesPromoCodeReservation;
use Illuminate\Contracts\Support\Responsable;

class CreateResponse implements Responsable
{
    /**
     * To Response
     *
     * @param \App\Http\Requests\Request $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function toResponse($request)
    {
        $agent_lead = AgentLead::find(request('agent_lead_id'));

        $tid = Lead::max('reference');
        $prefixes = prefixesArray(['lead']);
        $customers = Customer::whereHas('currency')->get(['id', 'company']);
        $branches = Branch::get(['id', 'name', 'customer_id']);
        $income_accounts = Account::whereHas('account_type_detail', fn($q) => $q->where('system_rel', 'income'))->get();
        $leadSources = LeadSource::select('id', 'name')->get();
        $classlists = Classlist::get();
        $currencies = Currency::get(['id', 'code']);


        $l1 = ThirdPartiesPromoCodeReservation::where('status', 'reserved')->get()->pluck('redeemable_code')->toArray();
        $l2 = CustomersPromoCodeReservation::where('status', 'reserved')->get()->pluck('redeemable_code')->toArray();
        $l3 = ReferralsPromoCodeReservation::where('status', 'reserved')->get()->pluck('redeemable_code')->toArray();

        $redeemableCodes = array_merge($l1, $l2, $l3);

        return view('focus.leads.create', 
            compact('currencies', 'classlists', 'agent_lead', 'tid', 'customers', 'branches', 'prefixes', 'income_accounts', 'leadSources', 'redeemableCodes')
        );
    }
}
