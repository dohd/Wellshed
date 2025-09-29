<?php

namespace App\Http\Responses\Focus\purchase;

use App\Models\additional\Additional;
use App\Models\classlist\Classlist;
use App\Models\import_request\ImportRequest;
use App\Models\pricegroup\Pricegroup;
use App\Models\purchase\Purchase;
use App\Models\purchase_requisition\PurchaseRequisition;
use App\Models\purchaseClass\PurchaseClass;
use App\Models\supplier\Supplier;
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
        $additionals = Additional::all();
        $pricegroups = Pricegroup::all();
        $warehouses = Warehouse::all();
        $last_tid = Purchase::where('ins', auth()->user()->ins)->max('tid');
        $supplier = Supplier::where('name', 'Walk-in')->first(['id', 'name']);
        $price_suppliers = Supplier::whereHas('products')->get(['id', 'name']);
        $purchase_classes = PurchaseClass::whereHas('budgets', fn ($q) => $q->where('budget', '>', 0))
            ->whereHas('budgets.financialYear')
            ->select('id', 'name','expense_category')
            ->get();
        $classlists = Classlist::all();
        $purchase_requisitions = PurchaseRequisition::where('status', 'approved')->orderBy('id','desc')->get();
        $import_requests = ImportRequest::orderBy('id','desc')->get();

        return view('focus.purchases.create', compact('purchase_classes','purchase_requisitions','import_requests', 'classlists', 'last_tid', 'additionals', 'supplier', 'pricegroups', 'warehouses','price_suppliers'));
    }
}
