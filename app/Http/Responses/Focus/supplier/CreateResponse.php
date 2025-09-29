<?php

namespace App\Http\Responses\Focus\supplier;

use App\Models\account\Account;
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
        // load A/P accounts
        // $local_acc = Account::whereHas('account_type_detail', fn($q) => $q->whereIn('system', ['payable', 'loan']))
        //     ->whereHas('currency', fn($q) => $q->where('rate', 1))
        //     ->first(['id', 'holder', 'currency_id']);
        $accounts = Account::whereHas('account_type_detail', fn($q) => $q->whereIn('system', ['payable', 'loan']))
            ->whereHas('currency')
            ->get(['id', 'holder', 'currency_id']);
        $payroll_accounts = Account::whereHas('account_type_detail', function($q) {
            $q->whereIn('system', ['salaries_payable', 'payroll_taxes_payable', 'health_insurance_payable', 'retirement_contribution_payable', 'other_payroll_payable']);
        })
        ->whereHas('currency')
        ->get(['id', 'holder', 'currency_id']);  
        $accounts = collect()->merge($accounts)->merge($payroll_accounts);
    
        return view('focus.suppliers.create', compact('accounts'));
    }
}