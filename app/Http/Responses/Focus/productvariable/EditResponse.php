<?php

namespace App\Http\Responses\Focus\productvariable;

use App\Models\productvariable\Productvariable;
use Illuminate\Contracts\Support\Responsable;

class EditResponse implements Responsable
{
    /**
     * @var App\Models\productvariable\Productvariable
     */
    protected $productvariables;

    /**
     * @param App\Models\productvariable\Productvariable $productvariables
     */
    public function __construct($productvariable)
    {
        $this->productvariable = $productvariable;
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
        $base_units = Productvariable::whereNull('base_unit_id')->get(['id', 'title', 'code']);

        return view('focus.productvariables.edit', compact('base_units'))->with([
            'productvariable' => $this->productvariable
        ]);
    }
}