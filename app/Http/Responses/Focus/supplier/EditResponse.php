<?php

namespace App\Http\Responses\Focus\supplier;

use App\Models\account\Account;
use Illuminate\Contracts\Support\Responsable;

class EditResponse implements Responsable
{
    /**
     * @var App\Models\supplier\Supplier
     */
    protected $supplier;

    /**
     * @param App\Models\supplier\Supplier $supplier
     */
    public function __construct($supplier)
    {
        $this->supplier = $supplier;
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
        $accounts = Account::where('account_type', 'Expense')->get(['id', 'holder']);

        return view('focus.suppliers.edit', compact('accounts'))->with(['supplier' => $this->supplier]);
    }
}