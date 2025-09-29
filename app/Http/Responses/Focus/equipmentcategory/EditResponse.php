<?php

namespace App\Http\Responses\Focus\equipmentcategory;

use Illuminate\Contracts\Support\Responsable;

class EditResponse implements Responsable
{
    /**
     * @var App\Models\region\Region
     */
    protected $equipmentcategory;

    /**
     * @param App\Models\region\Region $region
     */
    public function __construct($equipmentcategory)
    {
        $this->equipmentcategory = $equipmentcategory;
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
        return view('focus.equipmentcategories.edit')->with([
            'equipmentcategory' => $this->equipmentcategory
        ]);
    }
}