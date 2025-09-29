<?php

namespace App\Http\Responses\Focus\customer;

use App\Models\account\Account;
use App\Models\currency\Currency;
use App\Models\customer\Customer;
use App\Models\customergroup\Customergroup;
use App\Models\customfield\Customfield;
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
        $input = $request->only('rel_type', 'rel_id');
        $customergroups = Customergroup::all();
        $customer = array();
        if (isset($input['rel_id'])) $customer = Customer::find($input['rel_id']);
        $fields = custom_fields(Customfield::where('module_id', '1')->get()->groupBy('field_type'));

        // load A/R accounts
        // $local_acc = Account::whereHas('account_type_detail', fn($q) => $q->whereIn('system', ['receivable', 'loan']))
        //     ->whereHas('currency', fn($q) => $q->where('rate', 1))
        //     ->first(['id', 'holder']);
        $accounts = Account::whereHas('account_type_detail', fn($q) => $q->whereIn('system', ['receivable', 'loan']))
            ->whereHas('currency')
            ->get(['id', 'holder']);
        // $accounts = collect(array_filter([$local_acc]))->merge($accounts);
        
        return view('focus.customers.create', compact('accounts', 'customergroups', 'fields', 'input', 'customer', 'accounts'));
    }
}
