<?php

namespace App\Http\Responses\Focus\projectequipment;

use Illuminate\Contracts\Support\Responsable;
use App\Models\equipment\Equipment;
use App\Models\region\Region;
use App\Models\branch\Branch;
use App\Models\section\Section;
use App\Models\jobschedule\Jobschedule;

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
         $input = $request->only('rel_type', 'rel_id');
         $rel_id=$input['rel_id'];
         $region=Region::all();
         $branch=Branch::all();
         $section=Section::all();
         $jobschedule=Jobschedule::find($input['rel_id']);
        
         if(isset($input['rel_id']))$equipment=Equipment::where('customer_id',$input['rel_id'])->get();
         
        return view('focus.projectequipments.create',compact('equipment','jobschedule','region','section','branch'));
    }
}