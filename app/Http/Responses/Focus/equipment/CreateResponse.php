<?php

namespace App\Http\Responses\Focus\equipment;

use App\Models\equipment\Equipment;
use App\Models\equipmentcategory\EquipmentCategory;
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
        $tid = Equipment::max('tid');
        $categories = EquipmentCategory::all();
        
        return view('focus.equipments.create', compact('tid', 'categories'));
    }
}