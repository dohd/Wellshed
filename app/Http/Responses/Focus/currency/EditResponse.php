<?php

namespace App\Http\Responses\Focus\currency;

use Illuminate\Contracts\Support\Responsable;

class EditResponse implements Responsable
{
    /**
     * @var App\Models\currency\Currency
     */
    protected $currency;

    /**
     * @param App\Models\currency\Currency $currency
     */
    public function __construct($currency)
    {
        $this->currency = $currency;
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
        return view('focus.currencies.edit')->with([
            'currency' => $this->currency
        ]);
    }
}