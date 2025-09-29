<?php

namespace App\Http\Responses\Focus\salary;

use App\Http\Controllers\Focus\jobGrade\JobGradeController;
use App\Http\Controllers\Focus\salary\SalaryController;
use App\Models\Access\User\User;
use App\Models\employeeDailyLog\EmployeeDailyLog;
use App\Models\hrm\Hrm;
use App\Models\salary\Salary;
use App\Repositories\Focus\salary\SalaryRepository;
use Illuminate\Contracts\Support\Responsable;
use App\Models\department\Department;
use App\Models\workshift\Workshift;
use Illuminate\Support\Facades\DB;

class CreateResponse implements Responsable
{
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

        $salariedEmployees = Salary::all()->pluck('employee_id');

        $employees = Hrm::where('ins', auth()->user()->ins)
            ->orderBy('first_name')
            ->whereNotIn('id', $salariedEmployees)
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

        return view('focus.salary.create', compact('workshifts', 'employees'));
    }
}