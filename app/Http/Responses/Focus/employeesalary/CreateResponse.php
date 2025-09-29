<?php

namespace App\Http\Responses\Focus\employeesalary;

use App\Models\allowance\Allowance;
use App\Models\hrm\Hrm;
use App\Models\nssf\Nssf;
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
        $nssfrates = Nssf::get()->pluck('name','id');
        $allowances = Allowance::where('status', 'Active')->get();
        $users = Hrm::where('status', 1)->orderBy('id', 'desc')->get();
     
        return view('focus.employeesalary.create',compact('users','nssfrates','allowances'));
    }
}