<?php

namespace App\Http\Responses\Focus\productvariable;

use App\Models\productvariable\Productvariable;
use Illuminate\Contracts\Support\Responsable;

class CreateResponse implements Responsable
{
    /**
     * To Response
     *
     * @param \App\Http\Requests\Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function toResponse($request)
    {
        $base_units = Productvariable::whereNull('base_unit_id')->get(['id', 'title', 'code']);

        return view('focus.productvariables.create', compact('base_units'));           
    }
}
