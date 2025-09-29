<?php

namespace App\Http\Responses\Focus\account;

use Illuminate\Contracts\Support\Responsable;
use App\Models\account\AccountType;
use App\Models\currency\Currency;

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
        $account_types = AccountType::orderBy('category', 'ASC')->get();
        $currencies = Currency::all();
        
        return view('focus.accounts.create', compact('currencies', 'account_types'));
    }
}
