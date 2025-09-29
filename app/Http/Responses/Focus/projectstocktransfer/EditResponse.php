<?php

namespace App\Http\Responses\Focus\projectstocktransfer;

use Illuminate\Contracts\Support\Responsable;

class EditResponse implements Responsable
{
    /**
     * @var App\Models\purchaseorder\Purchaseorder
     */
    protected $project;

    /**
     * @param App\Models\purchaseorder\Purchaseorder $purchaseorder
     */
    public function __construct($project)
    {
        $this->project = $project;
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
        $project = $this->project;

        return view('focus.projectstocktransfaer.edit', compact('project'));
    }
}