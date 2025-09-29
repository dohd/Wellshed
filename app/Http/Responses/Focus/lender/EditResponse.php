<?php

namespace App\Http\Responses\Focus\lender;

use Illuminate\Contracts\Support\Responsable;

class EditResponse implements Responsable
{
    /**
     * @var App\Models\lender\Lender
     */
    protected $lender;

    /**
     * @param App\Models\lender\Lender $lender
     */
    public function __construct($lender)
    {
        $this->lender = $lender;
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
      

        return view('focus.lenders.edit')
            ->with(['lenders' => $this->lender,]);
    }
}
