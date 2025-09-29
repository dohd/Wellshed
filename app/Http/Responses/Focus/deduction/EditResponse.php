<?php

namespace App\Http\Responses\Focus\deduction;

use Illuminate\Contracts\Support\Responsable;

class EditResponse implements Responsable
{
    /**
     * @var App\Models\deduction\deduction
     */
    protected $deductions;

    /**
     * @param App\Models\deduction\deduction $deductions
     */
    public function __construct($deductions)
    {
        $this->deductions = $deductions;
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
        return view('focus.deduction.edit')->with([
            'deductions' => $this->deductions
        ]);
    }
}