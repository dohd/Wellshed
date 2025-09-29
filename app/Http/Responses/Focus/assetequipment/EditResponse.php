<?php

namespace App\Http\Responses\Focus\assetequipment;

use Illuminate\Contracts\Support\Responsable;
use App\Models\account\Account;

class EditResponse implements Responsable
{
    /**
     * @var App\Models\assetequipment\Assetequipment
     */
    protected $assetequipment;

    /**
     * @param App\Models\assetequipment\Assetequipment $assetequipment
     */
    public function __construct($assetequipment)
    {
        $this->assetequipment = $assetequipment;
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
        $assetequipment = $this->assetequipment;
        $assetequipment->cost = number_format($assetequipment->cost, 2, '.', '');
        $assetequipment->qty = number_format($assetequipment->qty, 1, '.', '');

        return view('focus.assetequipments.edit', compact('assetequipment'));
    }
}
