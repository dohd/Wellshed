<?php

namespace App\Http\Responses\Focus\purchase;

use App\Models\additional\Additional;
use App\Models\classlist\Classlist;
use App\Models\import_request\ImportRequest;
use App\Models\pricegroup\Pricegroup;
use App\Models\purchase_requisition\PurchaseRequisition;
use App\Models\purchaseClass\PurchaseClass;
use App\Models\supplier\Supplier;
use App\Models\warehouse\Warehouse;
use Illuminate\Contracts\Support\Responsable;

class EditResponse implements Responsable
{
    /**
     * @var App\Models\purchaseorder\Purchaseorder
     */
    protected $purchase;

    /**
     * @param App\Models\purchaseorder\Purchaseorder $purchaseorders
     */
    public function __construct($purchase)
    {
        $this->purchase = $purchase;
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
        $purchase = $this->purchase;
        $additionals = Additional::all();
        $pricegroups = Pricegroup::all();
        $warehouses = Warehouse::all();
        $supplier = Supplier::where('name', 'Walk-in')->first(['id', 'name']);
        $price_suppliers = Supplier::all();
        $purchase_classes = PurchaseClass::whereHas('budgets', fn ($q) => $q->where('budget', '>', 0))
            ->whereHas('budgets.financialYear')
            ->select('id', 'name','expense_category')
            ->get();
        $classlists = Classlist::all();
        $import_requests = ImportRequest::orderBy('id','desc')->get();
        $purchase_requisitions = PurchaseRequisition::where('status', 'approved')->orderBy('id','desc')->get();
         foreach ($purchase->products as $purchase_items) {
            if ($purchase_items->project){
                $quote_tid = !$purchase_items->project->quote ?: gen4tid('QT-', $purchase_items->project->quote->tid);
                $customer = !$purchase_items->project->customer ?: $purchase_items->project->customer->company;
                $branch = !$purchase_items->project->branch ?: $purchase_items->project->branch->name;
                $project_tid = gen4tid('PRJ-', $purchase_items->project->tid);
                $project = $purchase_items->project->name;
                $customer_branch = "{$customer}" .'-'. "{$branch}";
                $purchase_items['project_name'] = "[" . $quote_tid ."]"." - " . $customer_branch. " - ".$project_tid." - ".$project;
            }
        }
        
        return view('focus.purchases.edit', compact('purchase_classes','purchase_requisitions','import_requests', 'classlists', 'purchase', 'additionals', 'pricegroups', 'price_suppliers', 'warehouses'));
    }
}
