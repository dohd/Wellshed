<?php

namespace App\Http\Responses\Focus\bank;

use App\Models\account\Account;
use Illuminate\Contracts\Support\Responsable;

class EditResponse implements Responsable
{
    /**
     * @var App\Models\bank\Bank
     */
    protected $bank;

    /**
     * @param App\Models\bank\Bank $bank
     */
    public function __construct($bank)
    {
        $this->bank = $bank;
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
        $accounts = Account::whereHas('account_type_detail', fn($q) => $q->where('system_rel', 'bank'))
            ->get(['id', 'holder']);
        return view('focus.banks.edit', compact('accounts'))
            ->with(['bank' => $this->bank]);
    }
}