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

namespace App\Http\Controllers\Focus\purchase_requisition;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\Access\User\User;
use App\Models\hrm\Hrm;
use App\Models\part\Part;
use App\Models\purchase_request\PurchaseRequest;
use App\Models\purchase_requisition\PurchaseRequisition;
use App\Models\stock_transfer\StockTransfer;
use App\Models\warehouse\Warehouse;
use App\Repositories\Focus\purchase_requisition\PurchaseRequisitionRepository;
use DB;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class PurchaseRequisitionsController extends Controller
{
    /**
     * variable to store the repository object
     * @var PurchaseRequisitionRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param PurchaseRequisitionRepository $repository ;
     */
    public function __construct(PurchaseRequisitionRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return new ViewResponse('focus.purchase_requisitions.index');
    }

    /**
     * Show the form for creating a new resource.
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $tid = PurchaseRequisition::where('ins', auth()->user()->ins)->max('tid');
        $users = Hrm::all();
        $fg_goods = Part::all();

        return view('focus.purchase_requisitions.create', compact('users', 'tid','fg_goods'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(Request $request)
    {
        // dd($request->all());
        $data = $request->only(['part_id','tid','employee_id','date','priority','expect_date','note','project_id','project_milestone_id','item_type','purchase_request_id']);
        $data_items = $request->only(['part_item_id','product_id','product_name','unit_id','qty','price','milestone_item_id','budget_item_id','remark','purchase_request_item_id','stock_qty','purchase_qty']);
        $data_items = modify_array($data_items);
        $data_items = array_filter($data_items, fn($v) => $v['purchase_qty'] > 0 || $v['stock_qty'] > 0);
        if (!$data_items) throw ValidationException::withMessages(['Line item totals are required']);
        try {
            $this->repository->create(compact('data','data_items'));
        } catch (\Throwable $th) {
            //throw $th;
            return errorHandler('Error Creating Requisition '.$th->getMessage(), $th);
        }

        return new RedirectResponse(route('biller.purchase_requisitions.index'), ['flash_success' => 'Purchase Requisition Created Successfully']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  PurchaseRequisition $purchase_requisition
     * @return \Illuminate\Http\Response
     */
    public function edit(PurchaseRequisition $purchase_requisition)
    {
        $users = Hrm::all();
        $fg_goods = Part::all();

        return view('focus.purchase_requisitions.edit', compact('purchase_requisition', 'users','fg_goods'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  PurchaseRequisition $purchase_requisition
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PurchaseRequisition $purchase_requisition)
    {
        $data = $request->only(['part_id','tid','employee_id','date','priority','expect_date','note','project_id','project_milestone_id','item_type']);
        $data_items = $request->only(['part_item_id','product_id','product_name','unit_id','qty','price','id','milestone_item_id','budget_item_id','remark','purchase_request_item_id','stock_qty','purchase_qty']);
        $data_items = modify_array($data_items);
        $data_items = array_filter($data_items, fn($v) => $v['purchase_qty'] > 0 || $v['stock_qty'] > 0);
        try {
            $this->repository->update($purchase_requisition, compact('data','data_items'));
        } catch (\Throwable $th) {
            //throw $th;
            return errorHandler('Error Updating Requisition '.$th->getMessage(), $th);
        }

        return new RedirectResponse(route('biller.purchase_requisitions.index'), ['flash_success' => 'Purchase Requisition Updated Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  PurchaseRequisition $purchase_requisition
     * @return \Illuminate\Http\Response
     */
    public function destroy(PurchaseRequisition $purchase_requisition)
    {
        $this->repository->delete($purchase_requisition);

        return new RedirectResponse(route('biller.purchase_requisitions.index'), ['flash_success' => 'Purchase Requisition Deleted Successfully']);
    }


    /**
     * Display the specified resource.
     *
     * @param  PurchaseRequisition $purchase_requisition
     * @return \Illuminate\Http\Response
     */
    public function show(PurchaseRequisition $purchase_requisition)
    {
        return view('focus.purchase_requisitions.view', compact('purchase_requisition'));
    }

    public function approve(Request $request)
    {
        try {
            $purchase_requisition = PurchaseRequisition::find($request->id);
            DB::beginTransaction();
            $data = $request->except(['_token','id']);
            if($data['status'] == 'approved')
            {
            
                $data['approved_date'] = date_for_database($data['approved_date']);
                $data['approved_by'] = auth()->user()->id;
            }
            $purchase_requisition->update($data);
            if ($purchase_requisition){
                DB::commit();
            }
            
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollback();
            return errorHandler('Error Approving Requisition '.$th->getMessage(), $th);
        }
        return back()->with('flash_success','Requisition Approved Successfully !!');
    }

    public function get_requisition_items(Request $request)
    {
        $purchase_requisition = PurchaseRequisition::find($request->requisition_id);
        $items = $purchase_requisition->items()
        ->with(['budget_item','unit'])
        ->select('id','product_id', 'product_name', 'qty as requisition_qty','budget_item_id','unit_id','issued_qty','stock_qty')
        ->whereRaw('stock_qty > issued_qty')
        ->where('stock_qty','>',0)
        ->get();
        

        $stock_transfers = StockTransfer::where('project_id', $purchase_requisition->project_id)
            ->where('status', 'Complete')->orWhere('status', 'Partial')
            ->with(['stock_rcvs.items' => function($query) {
                $query->select('stock_rcv_items.id','stock_rcv_items.stock_rcv_id', 'stock_rcv_items.productvar_id', 'stock_rcv_items.qty_rcv','stock_rcv_items.item_id');
            }])
            ->get();

        $mapped_items = $items->map(function($requisition_item) use ($stock_transfers) {
            $total_stock_receive_qty = 0;
            $itemId = 0;
            
            foreach ($stock_transfers as $stock_transfer) {
                foreach ($stock_transfer->stock_rcvs as $stock_receive) {
                    foreach ($stock_receive->items as $stock_receive_item) {
                        // Check if the product_id matches
                       
                        if ($stock_receive_item->productvar_id == $requisition_item->product_id) {
                            $total_stock_receive_qty += $stock_receive_item->qty_rcv;
                            $itemId = $stock_receive_item->item_id;
                        }
                    }
                }
            }

            // If no matching stock_receive_items, total_stock_receive_qty will remain 0
            return [
                'requisition_item_id' => $requisition_item->id,
                'item_id' => $itemId,
                'id' => $requisition_item->product_id,
                'name' => $requisition_item->product_name,
                'code' => $requisition_item->product ? $requisition_item->product->code : '',
                'requested_qty' => $requisition_item->stock_qty,
                'purchase_price' => fifoCost($requisition_item->product_id) ?: $requisition_item->product->purchase_price,
                'booked_qty' => $total_stock_receive_qty, // 0 if no matching stock_receive_items found
                'budget_qty' => $requisition_item->budget_item ? $requisition_item->budget_item->new_qty : 0,
                'budget_item_id' => $requisition_item->budget_item_id,
                'issued_qty' => $requisition_item->issued_qty,
                // 'issued_qty' => $requisition_item->budget_item ? $requisition_item->budget_item->issue_qty : 0,
                'unit' => @$requisition_item->unit,
                'warehouses' => Warehouse::whereHas('products', fn($q) => $q->where('name', 'LIKE', "%{$requisition_item->product_name}%"))
                ->with(['products' => fn($q) => $q->where('name', 'LIKE', "%{$requisition_item->product_name}%")])
                ->get()
                ->map(function($wh) {
                    $wh->products_qty = $wh->products->sum('qty');
                    unset($wh->product);
                    return $wh;
                }),
            ];
        });

    
        return response()->json($mapped_items);
    }

    public function get_requests(Request $request)
    {
        $purchase_requests = PurchaseRequest::where('status', 'approved')->get();
        return DataTables::of($purchase_requests)
        ->escapeColumns(['id'])
        ->addIndexColumn()    
        ->addColumn('tid', function ($purchase_request) {
            return gen4tid('REQ-', $purchase_request->tid);
        })
        ->addColumn('date', function ($purchase_request) {
            return dateFormat($purchase_request->date);
        })
        ->addColumn('employee', function ($purchase_request) {
            if ($purchase_request->employee) 
            return $purchase_request->employee->full_name;
        })
         ->addColumn('project', function ($purchase_request) {
            $purchase_request['project_name'] = '';
            if ($purchase_request->project){
                $quote_tid = !$purchase_request->project->quote ?: gen4tid('QT-', $purchase_request->project->quote->tid);
                $customer = !$purchase_request->project->customer ?: $purchase_request->project->customer->company;
                $branch = !$purchase_request->project->branch ?: $purchase_request->project->branch->name;
                $project_tid = gen4tid('PRJ-', $purchase_request->project->tid);
                $project = $purchase_request->project->name;
                $customer_branch = "{$customer}" .'-'. "{$branch}";
                $purchase_request['project_name'] = "[" . $quote_tid ."]"." - " . $customer_branch. " - ".$project_tid." - ".$project;
            }else{
                if($purchase_request->status == 'approved') {
                    $purchase_request['project_name'] = $purchase_request->status_note;
                }else{

                    $purchase_request['project_name'] = $purchase_request->note;
                }
            }
            return $purchase_request['project_name'];
        })
        ->addColumn('expect_date', function ($purchase_request) {
            return dateFormat($purchase_request->expect_date);
        })
        ->addColumn('actions', function ($purchase_request) {
            $btn = '<a href="'.route('biller.purchase_requisitions.create_pr', [$purchase_request->id]).'" class="btn btn-purple round" data-toggle="tooltip" data-placement="top" title="Print"><i class="fa fa-plus"></i></a> ';
            return $btn;
        })
        ->make(true);
    }

    public function create_pr($purchase_request_id)
    {
        $purchase_request = PurchaseRequest::find($purchase_request_id);
        $tid = PurchaseRequisition::where('ins', auth()->user()->ins)->max('tid');
        $users = Hrm::all();
        $fg_goods = Part::all();
        return view('focus.purchase_requisitions.create_pr', compact('purchase_request','users','fg_goods'));
    }

    public function items(Request $request)
    {
        $purchaseRequisitionIds = $request->input('purchase_requisition_ids', []);

        if (!is_array($purchaseRequisitionIds) || empty($purchaseRequisitionIds)) {
            return response()->json(['error' => 'Invalid or empty purchase_requisition_ids'], 400);
        }

        // Fetch purchase requisitions
        $purchaseRequisitions = PurchaseRequisition::whereIn('id', $purchaseRequisitionIds)->get();

        $pr_items = [];
        foreach($purchaseRequisitions as $req)
        {
            foreach($req->items as $item)
            {
                if($item->purchase_qty > 0){
                    $pr_items[] = [
                        'product_name' => $item->product_name,
                        'product_id' => $item->product_id,
                        'project_id' => $item->project_id ?? 0,
                        'project_milestone_id' => $req->project_milestone_id ?? 0,
                        'qty' => $item->purchase_qty,
                        'uom' => $item->unit ? $item->unit->code : '',
                        'purchase_requisition_item_id' => $item->id,
                    ];
                }
                
            }
        }

        return response()->json($pr_items);
    }

    public function get_items(Request $request)
    {
        $purchase_requisition = PurchaseRequisition::where('id', $request->purchase_requisition_id)->first();

        $pr_items = [];
        $exempted_count = 0;
        foreach($purchase_requisition->items as $item)
        {
            if($item->purchase_qty > 0){
                if (@$item->product->product->stock_type === 'generic' || @$item->product->product->stock_type === 'service') {
                    $exempted_count++;
                    continue;
                }
                $item['project_name'] = '';
                if ($item->project){
                    $quote_tid = !$item->project->quote ?: gen4tid('QT-', $item->project->quote->tid);
                    $customer = !$item->project->customer ?: $item->project->customer->company;
                    $branch = !$item->project->branch ?: $item->project->branch->name;
                    $project_tid = gen4tid('PRJ-', $item->project->tid);
                    $project = $item->project->name;
                    $customer_branch = "{$customer}" .'-'. "{$branch}";
                    $item['project_name'] = "[" . $quote_tid ."]"." - " . $customer_branch. " - ".$project_tid." - ".$project;
                }
    
                $pr_items[] = [
                    'product_name' => $item->product_name,
                    'product_id' => $item->product_id,
                    'product_code' => @$item->product->code,
                    'project_id' => $item->project_id ?? 0,
                    'project_name' => $item['project_name'],
                    'project_milestone_id' => $req->project_milestone_id ?? 0,
                    'qty' => $item->purchase_qty,
                    'uom' => $item->unit ? $item->unit->code : '',
                    'price' => fifoCost($item->product_id) > 0 ? fifoCost($item->product_id) : @$item->product->purchase_price,
                ];
            }
            
        }

        return response()->json(
            [
                'items' => $pr_items,
                'exempted_generic_count' => $exempted_count,
            ]
        );
    }

    public function get_project(Request $request)
    {
        $purchase_requisition = PurchaseRequisition::where('id', $request->requisition_id)->first();
        
        $pr_tid = gen4tid('PR-', $purchase_requisition->tid);
        $pr_name = $purchase_requisition->note;
        $project_tid = $purchase_requisition->project ? gen4tid('PRJ-',$purchase_requisition->project->tid) : '';
        $project_name = $purchase_requisition->project ? $purchase_requisition->project->name : '';
        $mr_tid = $purchase_requisition->purchase_request ? gen4tid('REQ-',$purchase_requisition->purchase_request->tid) : '';

        $full = $pr_tid . ' ' .$pr_name. ' '. $mr_tid . ' '.$project_tid.' '.$project_name;
        return response()->json($full);
    }

    public function get_pr_requisitions(Request $request)
    {
        $type = $request->type;
        $id = $request->id;

        if ($type == 'Project') {
            $prs = PurchaseRequisition::where('project_id', $id)->orderBy('id', 'desc')->get();
        } elseif ($type == 'Customer') {
            $prs = PurchaseRequisition::whereHas('project', function ($q) use ($id) {
                $q->where('customer_id', $id);
            })->orderBy('id', 'desc')->get();
        } 
        elseif ($type == 'Employee' || $type == 'Default') {
            $prs = PurchaseRequisition::whereNull('project_id')->where('employee_id', $id)->orderBy('id', 'desc')->get();
        } 
        else if($type == 'Finished Goods')
        {
          $prs = PurchaseRequisition::where('part_id',$id)->orderBy('id', 'desc')->get();   
        }
        else {
            $prs = collect(); // Return an empty collection if no type matches
        }

        // Applying the map function
        $prs = $prs->map(function ($pr) {
            // $pr_tid = gen4tid('PR-', $pr->tid);
            $pr_tid = '';
            if ($pr->pr_parent_id) {
                $pr_tid = gen4tid('PR-', $pr->pr_parent->tid) . 'B';
            }elseif ($pr->pr_child) {
                $pr_tid = gen4tid('PR-', $pr->tid) . 'A';
            }else{
                $pr_tid = gen4tid('PR-', $pr->tid);
            }
            $pr_name = $pr->note;
            $project_tid = $pr->project ? gen4tid('PRJ-',$pr->project->tid) : '';
            $project_name = $pr->project ? $pr->project->name : '';
            $mr_tid = $pr->purchase_request ? gen4tid('REQ-',$pr->purchase_request->tid) : '';
    
            $full = $pr_tid . ' | ' .$pr_name. ' | '. $mr_tid . ' | '.$project_tid.' | '.$project_name;
            return [
                'id' => $pr->id,
                'name' => $full,
            ];
        });

        return response()->json($prs);
    }

    public function create_pr_copy($pr_id)
    {
        $purchase_requisition = PurchaseRequisition::find($pr_id);
        try {
            $this->repository->create_pr_copy($purchase_requisition);
        } catch (\Throwable $th) {
            return errorHandler('Error Creating New PR', $th);
        }
        return back()->with('flash_success','Purchase Requisition Generated Successfully!!');
    }

}
