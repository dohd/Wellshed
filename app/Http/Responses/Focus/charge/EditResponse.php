<?php

namespace App\Http\Responses\Focus\charge;

use App\Models\account\Account;
use Illuminate\Contracts\Support\Responsable;
use App\Models\customer\Customer;

class EditResponse implements Responsable
{
    /**
     * @var App\Models\productcategory\Productcategory
     */
    protected $charge;

    /**
     * @param App\Models\productcategory\Productcategory $productcategories
     */
    public function __construct($charge)
    {
        $this->charge = $charge;
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
        $customers = Customer::all();
        $bank_accounts = Account::whereHas('currency', fn($q) => $q->where('rate', 1))
            ->whereIn('account_type_id', [6])
            ->get(['id', 'holder', 'number', 'account_type_id']);
        $exp_accounts = Account::whereHas('account_type_detail', fn($q) => $q->where('system', 'bank_service_fees'))
            ->get();

        return view('focus.charges.edit')->with([
            'charge' => $this->charge,
            'customers' => $customers,
            'bank_accounts' => $bank_accounts,
            'exp_accounts' => $exp_accounts,
        ]);
    }
}
