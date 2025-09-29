<?php

namespace App\Http\Responses\Focus\openingbalance;

use Illuminate\Contracts\Support\Responsable;

class EditResponse implements Responsable
{
    /**
     * @var App\Models\product\Product
     */
    protected $openingbalance;

    /**
     * @param App\Models\product\Product $products
     */
    public function __construct($openingbalance)
    {
        $this->openingbalance = $openingbalance;
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
        /*$fields_raw=get_custom_fields(3,$this->products->id);
         $fields_data = custom_fields($fields_raw);
        return view('focus.products.edit')->with([
            'products' => $this->products,'fields_data'=>$fields_data
        ])->with(product_helper());*/
    }
}