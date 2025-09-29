<?php

namespace App\Http\Responses\Focus\payroll;

use Illuminate\Contracts\Support\Responsable;

class EditResponse implements Responsable
{
    /**
     * @var App\Models\payroll\payroll
     */
    protected $payroll;

    /**
     * @param App\Models\payroll\payroll $payrolls
     */
    public function __construct($payroll)
    {
        $this->payroll = $payroll;
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
        return view('focus.payroll.edit')->with([
            'payroll' => $this->payroll
        ]);
    }
}