<?php

namespace App\Http\Responses\Focus\charge;

use App\Models\account\Account;
use App\Models\charge\Charge;
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
        $payment_modes = ['Cash', 'Bank Transfer', 'Cheque', 'Mpesa', 'Card' ];
        $last_charge = Charge::orderBy('id', 'desc')->first(['tid']);
        $bank_accounts = Account::whereHas('currency', fn($q) => $q->where('rate', 1))
            ->whereIn('account_type_id', [6])
            ->get(['id', 'holder', 'number', 'account_type_id']);
        $exp_accounts = Account::whereHas('account_type_detail', fn($q) => $q->where('system', 'bank_service_fees'))
            ->get();
    
        return view('focus.charges.create', compact('last_charge', 'bank_accounts', 'exp_accounts', 'payment_modes'));
    }
}
