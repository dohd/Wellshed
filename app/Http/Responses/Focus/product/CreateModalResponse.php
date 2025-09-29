<?php

namespace App\Http\Responses\Focus\product;

use App\Models\customfield\Customfield;
use Illuminate\Contracts\Support\Responsable;

class CreateModalResponse implements Responsable
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
          //$product_name = !empty(request()->input('product_name'))? request()->input('product_name') : '';
        return view('focus.modal.product')->with(product_helper());
    }
}