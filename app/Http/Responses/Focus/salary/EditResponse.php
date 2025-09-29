<?php

namespace App\Http\Responses\Focus\salary;

use App\Http\Controllers\Focus\jobGrade\JobGradeController;
use App\Http\Controllers\Focus\salary\SalaryController;
use App\Models\hrm\Hrm;
use App\Models\salaryHistory\SalaryHistory;
use App\Models\workshift\Workshift;
use App\Repositories\Focus\salary\SalaryRepository;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Facades\DB;

class EditResponse implements Responsable
{
    /**
     * @var App\Models\salary\salary
     */
    protected $salary;

    /**
     * @param App\Models\salary\salary $salary
     */
    public function __construct($salary)
    {
        $this->salary = $salary;
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
        $workshifts = Workshift::all(['id','name']);

        $employees = Hrm::where('ins', auth()->user()->ins)
            ->orderBy('first_name')
            ->select(
                'id',
                'first_name',
                'last_name',
            )
            ->get()
            ->map(function ($emp) {

                $user = $emp->user;

                $grade = optional(optional($emp->meta)->employeeJobTitle)->job_grade;// ? $emp->meta->jobtitle->job_grade : false;
                $jobGrade = $grade ? (new JobGradeController())->getJobGrade($grade) : 'No Job Grade Set';

                return [
                    'grade' => $jobGrade,
                    'id' => $emp->id,
                    'full_name' => $emp->first_name . " " . $emp->last_name . " || " . $jobGrade,
                ];
            })
            ->toArray();

        $salaryHistory = SalaryHistory::where('salary_id', $this->salary->id)->get();

        return view('focus.salary.edit')->with([
            'employees' => $employees,
            'salary' => $this->salary,
            'salaryHistory' => $salaryHistory,
            'workshifts' => $workshifts
        ]);
    }
}