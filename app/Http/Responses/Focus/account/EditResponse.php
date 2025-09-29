<?php

namespace App\Http\Responses\Focus\account;

use App\Models\account\Account;
use App\Models\account\AccountType;
use App\Models\currency\Currency;
use Illuminate\Contracts\Support\Responsable;

class EditResponse implements Responsable
{
    /**
     * @var App\Models\account\Account
     */
    protected $accounts;

    /**
     * @param App\Models\account\Account $accounts
     */
    public function __construct($accounts)
    {
        $this->accounts = $accounts;
    }

    /**
     * To Response
     *
     * @param \App\Http\Requests\Request $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function toResponse($request)
    {
        $account = $this->accounts;
        $account->load('account_type_detail');
        $account->parent_account = Account::find($account->parent_id);
        $account_types = AccountType::orderBy('category', 'ASC')->get();
        $currencies = Currency::all();

        return view('focus.accounts.edit', compact('currencies', 'account_types', 'account'));
    }
}