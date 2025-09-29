<?php

namespace App\Http\Responses\Focus\purchaseorder;

use App\Models\additional\Additional;
use App\Models\hrm\Hrm;
use App\Models\import_request\ImportRequest;
use App\Models\pricegroup\Pricegroup;
use App\Models\purchase_requisition\PurchaseRequisition;
use App\Models\purchaseClass\PurchaseClass;
use App\Models\purchaseorder\Purchaseorder;
use App\Models\supplier\Supplier;
use App\Models\term\Term;
use App\Models\warehouse\Warehouse;
use Illuminate\Contracts\Support\Responsable;
use App\Models\rfq\RfQ;

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
        $prefixes = prefixesArray(['purchase_order'], auth()->user()->ins);
        $last_tid = Purchaseorder::max('tid');
        $warehouses = Warehouse::all();
        $additionals = Additional::all();
        $pricegroups = Pricegroup::all();
        $terms = Term::where('type', 4)->get(); // Purchase order
        $price_supplier = Supplier::whereHas('products')->get(['id', 'name']);
        $purchaseClasses = PurchaseClass::whereHas('budgets', fn ($q) => $q->where('budget', '>', 0))
            ->whereHas('budgets.financialYear')
            ->select('id', 'name', 'expense_category')
            ->get();
        $rfqs = RFQ::where('status', 'approved')->get();
        $purchase_requisitions = PurchaseRequisition::where('status', 'approved')->orderBy('id','desc')->get();
        $users = Hrm::all();
        $import_requests = ImportRequest::orderBy('id','desc')->get();
        $params = compact('last_tid','warehouses','import_requests', 'additionals', 'pricegroups','price_supplier','price_supplier', 'terms', 'prefixes', 'purchaseClasses','rfqs','purchase_requisitions','users');
        return view('focus.purchaseorders.create', $params);
    }
}
