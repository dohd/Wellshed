<?php

namespace App\Http\Responses\Focus\jobtitle;

use App\Http\Controllers\Focus\jobGrade\JobGradeController;
use App\Models\department\Department;
use Illuminate\Contracts\Support\Responsable;

class EditResponse implements Responsable
{
    /**
     * @var App\Models\jobtitle\JobTitle
     */
    protected $jobtitles;

    /**
     * @param App\Models\jobtitle\jobtitle $jobtitles
     */
    public function __construct($jobtitles)
    {
        $this->jobtitles = $jobtitles;
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

        $jobGrades = (new JobGradeController())->getJobGradesArray();
        $departments = Department::all();


        return view('focus.jobtitle.edit')->with([
            'jobtitles' => $this->jobtitles, 'jobGrades' => $jobGrades, 'departments' => $departments
        ]);
    }
}