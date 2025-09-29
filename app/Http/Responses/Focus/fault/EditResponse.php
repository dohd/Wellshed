<?php

namespace App\Http\Responses\Focus\fault;

use Illuminate\Contracts\Support\Responsable;

class EditResponse implements Responsable
{
    /**
     * @var App\Models\fault\JobTitle
     */
    protected $faults;

    /**
     * @param App\Models\fault\fault $faults
     */
    public function __construct($faults)
    {
        $this->faults = $faults;
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
        return view('focus.fault.edit')->with([
            'faults' => $this->faults
        ]);
    }
}