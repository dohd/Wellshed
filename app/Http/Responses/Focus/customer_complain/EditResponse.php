<?php

namespace App\Http\Responses\Focus\customer_complain;

use App\Models\customer\Customer;
use App\Models\hrm\Hrm;
use Illuminate\Contracts\Support\Responsable;

class EditResponse implements Responsable
{
    /**
     * @var App\Models\customer_complain\CustomerComplain
     */
    protected $customer_complains;

    /**
     * @param App\Models\customer_complain\CustomerComplain $customer_complains
     */
    public function __construct($customer_complains)
    {
        $this->customer_complains = $customer_complains;
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
        $employees = Hrm::where('ins', auth()->user()->ins)->get();
        return view('focus.customer_complains.edit', compact('customers', 'employees'))->with([
            'customer_complains' => $this->customer_complains
        ]);
    }
}