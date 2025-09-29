<?php

namespace App\Http\Responses\Focus\withholding;

use App\Models\customer\Customer;
use App\Models\withholding\Withholding;
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
        $last_tid = Withholding::max('tid');
        $customers = Customer::whereHas('currency', fn($q) => $q->where('rate', 1))->get(['id', 'company', 'name']);
        return view('focus.withholdings.create', compact('customers', 'last_tid'));
    }
}
