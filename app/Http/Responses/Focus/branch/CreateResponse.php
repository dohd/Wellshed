<?php

namespace App\Http\Responses\Focus\branch;

use App\Models\customer\Customer;
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
        return view('focus.branches.create', compact('customers'));
    }
}
