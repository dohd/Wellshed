<?php
/*
 * Rose Business Suite - Accounting, CRM and POS Software
 * Copyright (c) UltimateKode.com. All Rights Reserved
 * ***********************************************************************
 *
 *  Email: support@ultimatekode.com
 *  Website: https://www.ultimatekode.com
 *
 *  ************************************************************************
 *  * This software is furnished under a license and may be used and copied
 *  * only  in  accordance  with  the  terms  of such  license and with the
 *  * inclusion of the above copyright notice.
 *  * If you Purchased from Codecanyon, Please read the full License from
 *  * here- http://codecanyon.net/licenses/standard/
 * ***********************************************************************
 */
namespace App\Http\Controllers\Focus\import_request;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\currency\Currency;
use App\Models\import_request\ImportRequest;
use App\Models\items\PurchaseItem;
use App\Models\items\PurchaseorderItem;
use App\Models\purchase_requisition\PurchaseRequisition;
use App\Models\supplier\Supplier;
use App\Repositories\Focus\import_request\ImportRequestRepository;

/**
 * ImportRequestsController
 */
class ImportRequestsController extends Controller
{
    /**
     * variable to store the repository object
     * @var ImportRequestRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param ImportRequestRepository $repository ;
     */
    public function __construct(ImportRequestRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\ImportRequest\
     * @return \App\Http\Responses\ViewResponse
     */
    public function index()
    {
        return new ViewResponse('focus.import_requests.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateImportRequestRequestNamespace $request
     * @return \App\Http\Responses\Focus\ImportRequest\CreateResponse
     */
    public function create()
    {
        $purchase_requisitions = PurchaseRequisition::where('status','approved')->orderBy('id','desc')->get();
        $last_tid = ImportRequest::max('tid');
        $suppliers = Supplier::get();
        return View('focus.import_requests.create', compact('purchase_requisitions','last_tid','suppliers'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreImportRequestRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(Request $request)
    {
        // dd($request->all());
        //Input received from the request
        $data = $request->only(['supplier_name','notes','date','due_date']);
        $purchase_requisition_ids = implode(',',$request->input('purchase_requisition_ids',[]));
        $data['purchase_requisition_ids'] = $purchase_requisition_ids;
        $data_items = $request->only(['product_name','product_id','unit','qty']);
        $data_items = modify_array($data_items);
        try {
            //Create the model using repository create method
            $this->repository->create(compact('data','data_items'));
        } catch (\Throwable $th) {dd($th);
            //throw $th;
            return errorHandler('Error Creating Import Request',$th);
        }
        //return with successfull message
        return new RedirectResponse(route('biller.import_requests.index'), ['flash_success' => 'Import Request Created Successfully!!']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\ImportRequest\ImportRequest $import_request
     * @param EditImportRequestRequestNamespace $request
     * @return \App\Http\Responses\Focus\ImportRequest\EditResponse
     */
    public function edit(ImportRequest $import_request)
    {
        $suppliers = Supplier::get();
        $purchase_requisitions = PurchaseRequisition::where('status','approved')->orderBy('id','desc')->get();
        return view('focus.import_requests.edit', compact('import_request','purchase_requisitions','suppliers'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateImportRequestRequestNamespace $request
     * @param App\Models\ImportRequest\ImportRequest $import_request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(Request $request, ImportRequest $import_request)
    {
        //Input received from the request
        $data = $request->only(['supplier_name','notes','date','due_date']);
        $purchase_requisition_ids = implode(',',$request->input('purchase_requisition_ids',[]));
        $data['purchase_requisition_ids'] = $purchase_requisition_ids;
        $data_items = $request->only(['product_name','product_id','unit','qty','id']);
        $data_items = modify_array($data_items);
        try {
            //Update the model using repository update method
            $this->repository->update($import_request, compact('data','data_items'));
        } catch (\Throwable $th) {
            //throw $th;
            return errorHandler('Error Updating Import Request', $th);
        }
        //return with successfull message
        return new RedirectResponse(route('biller.import_requests.index'), ['flash_success' => 'Import Request Updated Successfully!!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteImportRequestRequestNamespace $request
     * @param App\Models\ImportRequest\ImportRequest $import_request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(ImportRequest $import_request)
    {
        //Calling the delete method on repository
        $this->repository->delete($import_request);
        //returning with successfull message
        return new RedirectResponse(route('biller.import_requests.index'), ['flash_success' => 'Import Request Deleted Successfully!!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteImportRequestRequestNamespace $request
     * @param App\Models\ImportRequest\ImportRequest $import_request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(ImportRequest $import_request)
    {

        //returning with successfull message
        return new ViewResponse('focus.import_requests.view', compact('import_request'));
    }

    public function edit_import_request($import_request_id)
    {
        $import_request = ImportRequest::find($import_request_id);
        $suppliers = Supplier::get();
        $purchase_items = PurchaseItem::where('import_request_id', $import_request_id)->get();
        $purchase_order_items = PurchaseorderItem::where('import_request_id', $import_request_id)->where('qty_received', '>',0)->get();
        $currencies = Currency::all();
        return view('focus.import_requests.edit_import_request', compact('import_request','suppliers','purchase_order_items','purchase_items','currencies'));
    }

    public function update_import_request($import_request_id, Request $request)
    {
        $import_request = ImportRequest::find($import_request_id);
        $data = $request->only(['total','item_cost','shipping_cost']);
        $data_items = $request->only([
            'rate','amount','cbm','total_cbm','cbm_percent','cbm_value','rate_percent',
            'rate_value','avg_cbm_rate_value','avg_rate_shippment','avg_rate_shippment_per_item','id'
        ]);
        $expense_items = $request->only([
            'expense_id','exp_qty','uom', 'exp_rate','currency_id','fx_curr_rate','fx_rate','e_id','lpo_expense_id'
        ]);
        // dd($expense_items);
        $data_items = modify_array($data_items);
        $expense_items = modify_array($expense_items);

        try {
            $this->repository->update_import_request($import_request, compact('data', 'data_items', 'expense_items'));
        } catch (\Throwable $th) {
            //throw $th;
            return errorHandler('Error Updating Import Request', $th);
        }
        return new RedirectResponse(route('biller.import_requests.index'), ['flash_success' => 'Import Request Updated Successfully!!']);
    }

    public function get_products(Request $req)
    {
        $import_request = ImportRequest::find($req->import_request_id);
        $items = $import_request->items()->get();
        return response()->json($items);
    }

    public function change_status(Request $request, $import_request_id)
    {
        try {
            $import_request = ImportRequest::find($import_request_id);
            $data = $request->only(['status','status_note']);
            $data['approved_by'] = auth()->user()->id;
            $data['approval_date'] = date_for_database(date('d-m-Y'));
            $import_request->update($data);
        } catch (\Throwable $th) {
            //throw $th;
            return errorHandler('Error Changing Status Import Request', $th);
        }
        return back()->with('flash_success', 'Import Request status changed successfully!!');
    }
}
