<?php

namespace App\Http\Responses\Focus\projectstocktransfer;
use App\Models\projectstocktransfer\Projectstocktransfer;
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

         //$last_invoice=Purchaseorder::orderBy('id', 'desc')->where('i_class','=',0)->first();
         $last_id=Projectstocktransfer::orderBy('id', 'desc')->first();
        return view('focus.projectstocktransfers.create')->with(array('last_id'=>$last_id))->with(bill_helper(3,9));
    }
}