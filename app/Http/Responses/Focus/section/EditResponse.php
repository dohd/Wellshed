<?php

namespace App\Http\Responses\Focus\section;

use Illuminate\Contracts\Support\Responsable;

class EditResponse implements Responsable
{
    /**
     * @var App\Models\region\Region
     */
    protected $section;

    /**
     * @param App\Models\region\Region $region
     */
    public function __construct($section)
    {
        $this->section = $section;
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
        return view('focus.sections.edit')->with([
            'section' => $this->section
    }
}