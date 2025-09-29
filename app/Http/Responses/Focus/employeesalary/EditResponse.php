<?php

namespace App\Http\Responses\Focus\employeesalary;

use Illuminate\Contracts\Support\Responsable;

class EditResponse implements Responsable
{
    /**
     * @var App\Models\employeesalary\EmployeeSalary
     */
    protected $employeesalary;

    /**
     * @param App\Models\employeesalary\EmployeeSalary $employeesalary
     */
    public function __construct($employeesalary)
    {
        $this->employeesalary = $employeesalary;
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
        return view('focus.employeesalaries.edit')->with([
            'employeesalary' => $this->employeesalary
        ]);
    }
}