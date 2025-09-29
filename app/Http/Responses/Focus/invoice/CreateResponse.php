<?php

namespace App\Http\Responses\Focus\invoice;

use App\Models\invoice\Invoice;
use Illuminate\Contracts\Support\Responsable;
use App\Models\purchase\Purchase;

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
        $input = $request->only(['sub','p']);
        if (isset($input['sub'])) {
            $last_invoice = Invoice::orderBy('id', 'desc')->where('i_class', '>', 1)->first();
        } else {
            $input['sub']=false;
            $last_invoice = Invoice::orderBy('id', 'desc')->where('i_class', '=', 0)->first();
        }
         $last_id=Purchase::orderBy('id', 'desc')->first();
        return view('focus.invoices.create')->with(array('last_id' => $last_id,'sub'=>$input['sub'],'p'=>$request->p))->with(bill_helper(1, 2));

    }
}