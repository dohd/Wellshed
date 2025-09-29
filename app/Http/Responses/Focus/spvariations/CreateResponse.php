<?php

namespace App\Http\Responses\Focus\spvariations;

use App\Models\product\ProductVariation;
use App\Models\pricegroup\Pricegroup;
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

        $input = $request->only(['rel_id']);
        $rel_id=$request->rel_id;

       $products = ProductVariation::with(['v_prices' => function($query) use ($rel_id){
    $query->where('pricegroup_id', $rel_id);
}])->get();


$pricegroup_name=Pricegroup::where('id', $rel_id)->first();

      


        return view('focus.spvariations.create')->with(array('products' => $products,'pricegroup_name'=>$pricegroup_name,'p'=>$request->rel_id))->with(bill_helper(1, 2));


        //return view('focus.spvariations.create')->with(product_helper());
    }
}