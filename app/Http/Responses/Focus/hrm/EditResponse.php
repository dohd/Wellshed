<?php

namespace App\Http\Responses\Focus\hrm;

use App\Http\Controllers\Focus\jobGrade\JobGradeController;
use App\Models\Access\Permission\Permission;
use App\Models\Access\Permission\PermissionUser;
use App\Models\Access\Role\Role;
use App\Models\classlist\Classlist;
use App\Models\department\Department;
use App\Models\hrm\HrmMeta;
use App\Models\jobGrade\JobGrade;
use App\Models\jobtitle\JobTitle;
use App\Models\workshift\Workshift;
use Illuminate\Contracts\Support\Responsable;

class EditResponse implements Responsable
{
    /**
     * @var App\Models\hrm\Hrm
     */
    protected $hrms;

    /**
     * @param App\Models\hrm\Hrm $hrms
     */
    public function __construct($hrms)
    {
        $this->hrms = $hrms;
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
        $prefixes = prefixesArray(['hrm'], auth()->user()->ins);

        $departments = Department::all()->pluck('name','id');
        $roles = Role::where('status', 1)->get();

        $hrm_metadata = $this->hrms->meta? $this->hrms->meta->toArray() : [];

        $hrms_mod = collect([$this->hrms->toArray()])->map(function ($v) use($hrm_metadata) {
            return array_merge(array_diff_key($v, array_flip(['meta'])), $hrm_metadata);
        })->first();
        $hrms = $this->hrms->fill($hrms_mod);
        $last_tid = $hrms->employee_no;

        $emp_role = !empty($this->hrms->role) ? $this->hrms->role->id : '';
        $permissions_all = Permission::whereHas('roles', function ($q) use ($emp_role) {
            $q->where('role_id', $emp_role);
        })->get()->toArray();

        $general['create'] = $this->hrms->id;

        $hrms['employee_no'] = gen4tid('EMP-',$hrms->tid);
        $permissions = PermissionUser::all()->keyBy('id')->where('user_id', $general['create'])->toArray();

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



        return view('focus.hrms.edit',compact('prefixes','hrms', 'roles', 'general', 'permissions_all', 'permissions', 'departments', 'last_tid', 'jobGrades', 'jobTitles', 'classLists','workshifts'));
    }
}