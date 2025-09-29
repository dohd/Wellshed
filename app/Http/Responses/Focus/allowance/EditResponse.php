<?php

namespace App\Http\Responses\Focus\allowance;

use Illuminate\Contracts\Support\Responsable;

class EditResponse implements Responsable
{
    /**
     * @var App\Models\department\Allowance
     */
    protected $allowance;

    /**
     * @param App\Models\allowance\Allowance $allowance
     */
    public function __construct($allowance)
    {
        $this->allowance = $allowance;
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
        return view('focus.allowance.edit')->with([
            'allowance' => $this->allowance
        ]);
    }
}