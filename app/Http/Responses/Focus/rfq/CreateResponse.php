<?php

namespace App\Http\Responses\Focus\rfq;

use App\Models\additional\Additional;
use App\Models\pricegroup\Pricegroup;
use App\Models\project\Budget;
use App\Models\purchase_request\PurchaseRequest;
use App\Models\purchase_requisition\PurchaseRequisition;
use App\Models\rfq\RfQ;
use App\Models\supplier\Supplier;
use App\Models\term\Term;
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
        $ins = auth()->user()->ins;
        $prefixes = prefixesArray(['rfq'], $ins);
        $last_tid = RfQ::all()->max('tid');
        $warehouses = Warehouse::all();
        $additionals = Additional::all();
        $pricegroups = Pricegroup::all();
        $supplier = Supplier::where('name', 'Walk-in')->first(['id', 'name']);
        $suppliers = Supplier::get();
        $price_supplier = Supplier::whereHas('products')->get(['id', 'name']);
        // Purchase order
        $terms = Term::where('type', 5)->get();
        $purchase_requisitions = PurchaseRequisition::where('status','approved')->get();

        $budgetItems = null;
        $budget = null;

        $purchaseRequestItems = null;
        $purchaseRequest = null;

        if (!empty($request->budget_id)) {

            $budget = Budget::find($request->budget_id);
            $budgetItems = $budget->items;
        }
        else if (!empty($request->purchase_request_id)) {

            $purchaseRequest = PurchaseRequest::find($request->purchase_request_id);
            $purchaseRequestItems = $purchaseRequest->items;
        }

//        return compact('last_tid','warehouses', 'additionals', 'pricegroups','price_supplier','price_supplier', 'terms', 'prefixes');

        return view('focus.rfq.create', compact('last_tid','suppliers','warehouses', 'additionals', 'pricegroups','price_supplier','price_supplier', 'terms', 'prefixes', 'budget', 'budgetItems', 'purchaseRequest', 'purchaseRequestItems','purchase_requisitions'));
    }
}
