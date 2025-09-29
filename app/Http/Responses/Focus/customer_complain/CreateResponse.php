<?php

namespace App\Http\Responses\Focus\customer_complain;

use App\Models\customer\Customer;
use App\Models\hrm\Hrm;
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
        $customers = Customer::all();
        $employees = Hrm::where('ins', auth()->user()->ins)->get();
        return view('focus.customer_complains.create', compact('customers','employees'));
    }
}