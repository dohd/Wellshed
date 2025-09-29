<?php

namespace App\Http\Responses\Focus\remark;

use App\Models\branch\Branch;
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
        $branches = Branch::get();
    
        return view('focus.remarks.create', compact('branches'));
    }
}
