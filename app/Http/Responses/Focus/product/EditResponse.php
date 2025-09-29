<?php

namespace App\Http\Responses\Focus\product;

use App\Models\account\Account;
use App\Models\productcategory\Productcategory;
use App\Models\productvariable\Productvariable;
use App\Models\warehouse\Warehouse;
use Illuminate\Contracts\Support\Responsable;

class EditResponse implements Responsable
{
    /**
     * @var App\Models\product\Product
     */
    protected $product;

    /**
     * @param App\Models\product\Product $product
     */
    public function __construct($product)
    {
        $this->product = $product;
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
        $productvariables = Productvariable::all();
        $warehouses = Warehouse::all();
        $product_categories = Productcategory::all();
        $compound_unit_ids = $this->product->units()
            ->where('unit_type', 'compound')
            ->pluck('product_variables.id')
            ->toArray();
        $accounts = Account::whereIn('account_type', ['Asset', 'Expense'])
            ->whereDoesntHave('accountType', fn($q) => $q->where('system', 'bank'))
            ->get(['id', 'number', 'holder', 'account_type', 'parent_id'])
            ->filter(fn($v) => !$v->has_sub_accounts);

        return view('focus.products.edit', compact('accounts', 'product_categories', 'productvariables', 'warehouses', 'compound_unit_ids'))
            ->with(['product' => $this->product]);
    }
}
