<?php

namespace App\Http\Responses\Focus\term;

use Illuminate\Contracts\Support\Responsable;

class EditResponse implements Responsable
{
    /**
     * @var App\Models\term\Term
     */
    protected $term;

    /**
     * @param App\Models\term\Term $term
     */
    public function __construct($term)
    {
        $this->term = $term;
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
        $term = $this->term;
        return view('focus.terms.edit', compact('term'));
    }
}