<?php

namespace App\Http\Responses\Focus\pricegroup;

use Illuminate\Contracts\Support\Responsable;

class EditResponse implements Responsable
{
    /**
     * @var App\Models\warehouse\Warehouse
     */
    protected $pricegroups;

    /**
     * @param App\Models\warehouse\Warehouse $warehouses
     */
    public function __construct($pricegroups)
    {
        $this->pricegroups = $pricegroups;
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
        return view('focus.pricegroups.edit')->with([
            'pricegroups' => $this->pricegroups
        ]);
    }
}