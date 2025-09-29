<?php

namespace App\Http\Responses\Focus\jobschedule;

use Illuminate\Contracts\Support\Responsable;

class EditResponse implements Responsable
{
    /**
     * @var App\Models\region\Region
     */
    protected $jobschedule;

    /**
     * @param App\Models\region\Region $region
     */
    public function __construct($jobschedule)
    {
        $this->jobschedule = $jobschedule;
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
        return view('focus.jobschedules.edit')->with([
            'jobschedule' => $this->jobschedule
        ]);
    }
}