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
namespace App\Http\Controllers\Focus\queuerequisition;

use App\Models\queuerequisition\QueueRequisition;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\queuerequisition\CreateResponse;
use App\Http\Responses\Focus\queuerequisition\EditResponse;
use App\Repositories\Focus\queuerequisition\QueueRequisitionRepository;
use App\Models\hrm\Hrm;
use App\Models\branch\Branch;
use App\Models\quote\Quote;
use App\Models\project\BudgetItem;
use App\Models\project\Budget;
use App\Models\customer\Customer;
use App\Models\product\ProductVariation;
use App\Models\supplier_product\SupplierProduct;

/**
 * queuerequisitionsController
 */
class QueueRequisitionController extends Controller
{
    /**
     * variable to store the repository object
     * @var queuerequisitionRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param queuerequisitionRepository $repository ;
     */
    public function __construct(QueueRequisitionRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\queuerequisition\Request $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index()
    {
        //$suppliers = Supplier::whereHas('supplier_products')->get(['id', 'company']);
        $suppliers = ProductVariation::get(['id', 'name'])->unique('name');
        $suppliers = [...$suppliers];
        $quotes = Quote::all();
        return new ViewResponse('focus.queuerequisition.index', compact('suppliers', 'quotes'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreatequeuerequisitionRequestNamespace $request
     * @return \App\Http\Responses\Focus\queuerequisition\CreateResponse
     */
    public function create(Request $request)
    {
        return new CreateResponse('focus.queuerequisition.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StorequeuerequisitionRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(Request $request)
    {
        // dd($request->all());
        $input = $request->only([
            'item_name', 'product_code', 'unit', 'qty_balance',
            'qty', 'budget_item_id', 'system_name'
        ]);
        //dd($input);
        $budget_item_id = $input['budget_item_id'][0];
        $budget_id = BudgetItem::where('id', $budget_item_id)->first()->budget_id;
        $quote_id_foreign = Budget::where('id', $budget_id)->first()->quote_id;
        //Get Quote Number
        $quote_id = Quote::where('id', $quote_id_foreign)->first()->tid;
        //Get project_id
        $project_id = Quote::where('id', $quote_id_foreign)->first()->project_quote_id;
       // dd($project_id);
        //Get client branch
        $branch_foreign_id = Quote::where('id', $quote_id_foreign)->first()->branch_id;
        $branch_name = Branch::where('id', $branch_foreign_id)->first();
        $client_name = Customer::where('id', $branch_name->customer_id)->first();
        $client_branch = $client_name->name .':'. $branch_name->name;
        $single_input['client_branch'] = $client_branch;
        $single_input['quote_id'] = $quote_id;
        $single_input['project_quote_id'] = $project_id;
        $single_input['ins'] = auth()->user()->ins;
        $single_input['user_id'] = auth()->user()->id;
        $input['item_qty'] = $input['qty'];
        $input['uom'] = $input['unit'];
       // unset($input['budget_item_id']);
        unset($input['qty']);
        unset($input['unit']);
        $input = modify_array($input);
       // $single_input = modify_array($single_input);

        //dd($input);
        //Create the model using repository create method
        $this->repository->create(compact('input','single_input'));
        //return with successfull messagetrans
        return new RedirectResponse(route('biller.queuerequisitions.index'), ['flash_success' => 'QueueRequisition Created Successfully']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\queuerequisition\queuerequisition $queuerequisition
     * @param EditqueuerequisitionRequestNamespace $request
     * @return \App\Http\Responses\Focus\queuerequisition\EditResponse
     */
    public function edit(QueueRequisition $queuerequisition)
    {
        return new EditResponse($queuerequisition);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdatequeuerequisitionRequestNamespace $request
     * @param App\Models\queuerequisition\queuerequisition $queuerequisition
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(QueueRequisition $queuerequisition, Request $request)
    {
       // dd($request->all());
        //Input received from the request
        $input = $request->except(['_token', 'ins']);

        $issuance_update = BudgetItem::find($input['budget_item_id']);
       // dd($input['budget_item_id']);
        $issuance_update->new_qty = $issuance_update->new_qty - $input['qty_balance'];
        $issuance_update->update();
        //Update the model using repository update method
        $this->repository->update($queuerequisition, $input);
        //return with successfull message
        return new RedirectResponse(route('biller.queuerequisitions.index'), ['flash_success' => 'QueueRequisition Updated Successfully!!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeletequeuerequisitionRequestNamespace $request
     * @param App\Models\queuerequisition\queuerequisition $queuerequisition
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(QueueRequisition $queuerequisition, Request $request)
    {
        //Calling the delete method on repository
        $this->repository->delete($queuerequisition);
        //returning with successfull message
        return new RedirectResponse(route('biller.queuerequisitions.index'), ['flash_success' => 'QueueRequisition Deleted Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeletequeuerequisitionRequestNamespace $request
     * @param App\Models\queuerequisition\queuerequisition $queuerequisition
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(QueueRequisition $queuerequisition, Request $request)
    {
        //returning with successfull message
        return new ViewResponse('focus.queuerequisition.view', compact('queuerequisition'));
    }
    public function select(Request $request)
    {
        $q = $request->keyword;
        $users = ProductVariation::with('product')->where('name', 'LIKE', '%'.$q.'%')
            ->orWhere('code', 'LIKE', '%'.$q.'')
            ->get(['id', 'name', 'code', 'qty', 'purchase_price']);

        return response()->json($users);
    }

    public function update_description(Request $request)
    {
       // dd($request->all());
        $queuerequisition = QueueRequisition::find($request->id);
        $queuerequisition->system_name = $request->system_name;
        $queuerequisition->product_code = $request->product_code;
        $queuerequisition->item_qty = $request->item_qty;
        $queuerequisition->update();
        return new RedirectResponse(route('biller.queuerequisitions.index'), ['flash_success' => 'QueueRequisition Description Added']);
    }

    public function select_queuerequisition(Request $request)
    {
        $q = $request->keyword;
        $users = QueueRequisition::where('quote_no', 'LIKE', '%'.$q.'%')
            ->orWhere('client_branch', 'LIKE', '%'.$q.'')
            ->groupBy('quote_no')->get(['id', 'quote_no', 'client_branch']);

        return response()->json($users);
    }

    public function goods(Request $request)
    {
        //dd($request->all());
        if($request->queuerequisition_id == 'all'){
            $req = $request->pricelist;
            $queuerequisition = QueueRequisition::where('status', '1')->with('queuerequisition_supplier')->
           whereHas('queuerequisition_supplier', function ($query) use ($req) {
           return $query->where('supplier_id', $req);
            })->get();
            return response()->json($queuerequisition);
        }
        $queuerequisition = QueueRequisition::where('quote_no',request('queuerequisition_id'))->where('status', '1')->get();
        
        //$stock_goods = $queuerequisition? $queuerequisition->goods()->where('type', 'Stock')->get() : [];

        return response()->json($queuerequisition);
    }
    public function status(Request $request)
    {
       // dd($request->all());
       foreach ($request->statusId as $status_id) {
       // dd($status_id);
        $queuerequisition = QueueRequisition::where('id', $status_id)->update(['status'=> '1']);
       }
        return new RedirectResponse(route('biller.queuerequisitions.index'), ['flash_success' => 'Pushed to Purchase']);
    }

    
}
