<?php

namespace App\Http\Responses\Focus\jobtitle;

use App\Http\Controllers\Focus\jobGrade\JobGradeController;
use App\Models\department\Department;
use Illuminate\Contracts\Support\Responsable;

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

        $jobGrades = (new JobGradeController())->getJobGradesArray();
        $departments = Department::all();

        return view('focus.jobtitle.create', compact('jobGrades', 'departments'));
    }
}