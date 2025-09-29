<?php

namespace App\Http\Responses\Focus\casual;

use App\Models\job_category\JobCategory;
use App\Models\wage_item\WageItem;
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
        $job_categories = JobCategory::all();
        $wageItems = WageItem::pluck('name', 'id')->toArray();
        return view('focus.casuals.create', compact('job_categories', 'wageItems'));
    }
}