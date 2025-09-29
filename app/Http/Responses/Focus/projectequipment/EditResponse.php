<?php

namespace App\Http\Responses\Focus\projectequipment;

use Illuminate\Contracts\Support\Responsable;

class EditResponse implements Responsable
{
    /**
     * @var App\Models\region\Region
     */
    protected $projectequipment;

    /**
     * @param App\Models\region\Region $region
     */
    public function __construct($projectequipment)
    {
        $this->projectequipment = $projectequipment;
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
        return view('focus.projectequipments.edit')->with([
            'projectequipment' => $this->projectequipment
        ]);
    }
}