<?php

namespace App\Http\Responses\Focus\casual;

use App\Models\job_category\JobCategory;
use App\Models\wage_item\WageItem;
use Illuminate\Contracts\Support\Responsable;

class EditResponse implements Responsable
{
    /**
     * @var App\Models\casual\casual
     */
    protected $casual;

    /**
     * @param App\Models\casual\casual $casual
     */
    public function __construct($casual)
    {
        $this->casual = $casual;
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
        $job_categories = JobCategory::all();
        $wageItems = WageItem::pluck('name', 'id')->toArray();
        $wageItemIds = $this->casual->wageItems->pluck('id')->toArray();

        return view('focus.casuals.edit', compact('job_categories', 'wageItems', 'wageItemIds'))
            ->with(['casual' => $this->casual]);
    }
}