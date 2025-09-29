<?php

namespace App\Http\Responses\Focus\region;

use Illuminate\Contracts\Support\Responsable;

class EditResponse implements Responsable
{
    /**
     * @var App\Models\region\Region
     */
    protected $region;

    /**
     * @param App\Models\region\Region $region
     */
    public function __construct($region)
    {
        $this->region = $region;
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
        return view('focus.regions.edit')->with([
            'region' => $this->region
        ]);
    }
}