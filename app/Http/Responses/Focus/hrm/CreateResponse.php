<?php

namespace App\Http\Responses\Focus\hrm;

use App\Http\Controllers\Focus\jobGrade\JobGradeController;
use App\Models\Access\Role\Role;
use App\Models\classlist\Classlist;
use App\Models\department\Department;

use App\Models\hrm\HrmMeta;
use App\Models\jobGrade\JobGrade;
use App\Models\jobtitle\JobTitle;
use App\Models\workshift\Workshift;
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
        $roles=Role::where('status',1)->where('ins', auth()->user()->ins)
        ->get();

        $departments = Department::all()->pluck('name','id');
        $positions = JobTitle::get(['id', 'name', 'department_id']);
        $general['create'] = 1;

        $jobGrades = (new JobGradeController())->getJobGradesArray();
        $jobTitles = JobTitle::select('name', 'id', 'job_grade')->get()
            ->map(function ($item) {

                return (object) [
                    'id' => $item->id,
                    'name' => $item->name,
                    'job_grade' => $item->job_grade ? (new JobGradeController())->getJobGrade($item->job_grade) : '',
                ];
            });

        $classLists = Classlist::all();
        $workshifts = Workshift::get(['id','name']);


        return view('focus.hrms.create', compact('roles','general','departments','positions', 'jobGrades', 'jobTitles', 'classLists','workshifts'));
    }
}