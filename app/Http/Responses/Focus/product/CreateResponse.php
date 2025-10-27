<?php

namespace App\Http\Responses\Focus\product;

use App\Models\account\Account;
use App\Models\product\ProductVariation;
use App\Models\productcategory\Productcategory;
use App\Models\productvariable\Productvariable;
use App\Models\warehouse\Warehouse;
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
        $product_categories = Productcategory::all();
        $warehouses = Warehouse::all();
        $productvariables = Productvariable::query()
            // ->when(config('services.efris.base_url'), fn($q) => $q->whereNotNull('efris_unit'))
            ->get();
        $accounts = Account::whereIn('account_type', ['Asset', 'Expense'])
            ->whereDoesntHave('accountType', fn($q) => $q->where('system', 'bank'))
            ->get(['id', 'number', 'holder', 'account_type', 'parent_id'])
            ->filter(fn($v) => !$v->has_sub_accounts);
        $products = ProductVariation::where('type','full')->get();

        return view('focus.products.create', compact('accounts', 'product_categories', 'productvariables', 'warehouses','products'));
    }
}