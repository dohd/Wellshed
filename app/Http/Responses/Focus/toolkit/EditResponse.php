<?php

namespace App\Http\Responses\Focus\toolkit;

use App\Models\warehouse\Warehouse;
use Illuminate\Contracts\Support\Responsable;
use App\Models\toolkit\ToolkitItems;

class EditResponse implements Responsable
{
    /**
     * @var App\Models\toolkit\toolkit
     */
    protected $toolkit;

    /**
     * @param App\Models\toolkit\toolkit $toolkit
     */
    public function __construct($toolkit)
    {
        $this->toolkit = $toolkit;
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
        $toolkit = $this->toolkit;
        // $tool = explode(",",$toolkit->toolname);
        $toolkit_items = ToolkitItems::where('toolkit_id',$toolkit->id)->get();
        return view('focus.toolkit.edit', compact('toolkit','toolkit_items'));
    }
}
