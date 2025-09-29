<?php

namespace App\Http\Responses\Focus\workshift;

use App\Models\warehouse\Warehouse;
use Illuminate\Contracts\Support\Responsable;
use App\Models\workshift\WorkshiftItems;

class EditResponse implements Responsable
{
    /**
     * @var App\Models\workshift\workshift
     */
    protected $workshift;

    /**
     * @param App\Models\workshift\workshift $workshift
     */
    public function __construct($workshift)
    {
        $this->workshift = $workshift;
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
        $workshift = $this->workshift;
        // $tool = explode(",",$workshift->toolname);
        $workshift_items = WorkshiftItems::where('workshift_id',$workshift->id)->get();
        return view('focus.workshift.edit', compact('workshift','workshift_items'));
    }
}
