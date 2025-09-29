<?php

namespace App\Http\Responses\Focus\branch;

use Illuminate\Contracts\Support\Responsable;
use App\Models\customer\Customer;

class EditResponse implements Responsable
{
    /**
     * @var App\Models\productcategory\Productcategory
     */
    protected $branches;

    /**
     * @param App\Models\productcategory\Productcategory $productcategories
     */
    public function __construct($branches)
    {
        $this->branches = $branches;
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
         $customers=Customer::all();
        return view('focus.branches.edit')->with([
            'branches' => $this->branches,'customers'=>$customers
        ]);
    }
}