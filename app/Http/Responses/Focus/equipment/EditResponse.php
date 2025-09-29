<?php

namespace App\Http\Responses\Focus\equipment;

use App\Models\equipmentcategory\EquipmentCategory;
use Illuminate\Contracts\Support\Responsable;

class EditResponse implements Responsable
{
    /**
     * @var App\Models\productcategory\Productcategory
     */
    protected $equipment;

    /**
     * @param App\Models\productcategory\Productcategory $productcategories
     */
    public function __construct($equipment)
    {
        $this->equipment = $equipment;
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
        $categories = EquipmentCategory::all();

        return view('focus.equipments.edit', ['equipment' => $this->equipment] + compact('categories'));
    }
}