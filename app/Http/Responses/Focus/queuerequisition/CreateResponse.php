<?php

namespace App\Http\Responses\Focus\queuerequisition;

use Illuminate\Contracts\Support\Responsable;
use App\Models\department\Department;

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
       // $dept_jobtitle = Department::whereHas('jobtitles')->get(['id', 'name']);
        return view('focus.queuerequisition.create');
    }
}