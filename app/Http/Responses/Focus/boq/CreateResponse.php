<?php

namespace App\Http\Responses\Focus\boq;

use App\Models\additional\Additional;
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
        $additionals = Additional::all();
        $customers = Customer::all();
        return view('focus.boqs.create', compact('additionals','customers'));
    }
}