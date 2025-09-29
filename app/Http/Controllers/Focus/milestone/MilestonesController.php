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
namespace App\Http\Controllers\Focus\milestone;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\hrm\Hrm;
use App\Models\items\PurchaseItem;
use App\Models\items\QuoteItem;
use App\Models\project\Budget;
use App\Models\project\BudgetItem;
use App\Models\project\MileStoneItem;
use App\Models\project\Project;
use App\Models\project\ProjectLog;
use App\Models\project\ProjectMileStone;
use App\Models\project\ProjectRelations;
use App\Models\purchase_request\PurchaseRequest;
use App\Models\quote\Quote;
use DB;
use Yajra\DataTables\Facades\DataTables;

/**
 * milestonesController
 */
class MilestonesController extends Controller
{
    

    /**
     * Display a listing of the resource.
     * @return \App\Http\Responses\ViewResponse
     */
    public function index()
    {
        return new ViewResponse('focus.projects.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreatemilestoneRequestNamespace $request
     * @return \App\Http\Responses\Focus\milestone\CreateResponse
     */
    public function create($project_id)
    {
        $project = Project::find($project_id);
        $employees = Hrm::where('ins', auth()->user()->ins)->get();
        return view('focus.projects.milestones.create', compact('project','employees'));
    }
    public function create_milestone($project_id)
    {
        $project = Project::with(['quotes.budget'])->find($project_id);
        // Get the budgets associated with the project's quotes
        $budgets = $project->quotes->map(function ($quote) {
            return $quote->budget;  // Get the budget for each quote
        });
        $employees = Hrm::where('ins', auth()->user()->ins)->get();
        return view('focus.projects.milestones.create', compact('project', 'budgets','employees'));
    }

    /**
     * Store a newly created resource in storage.
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(Request $request)
    {
        $dataInput = $request->input('data', []);
        // dd($dataInput);
 
        $unit_id = [];
        $product_id = [];
        $qty = [];
        $budget_item_id = [];
        $price = [];

        $datas = [];
        foreach($dataInput as $input){
            
            if ($input['name'] == 'name') {
                $datas['name'] = $input['value'];
            }
            
            if ($input['name'] == 'description') {
                $datas['description'] = $input['value'];
            }
            
            if ($input['name'] == 'start_date') {
                $datas['start_date'] = $input['value'];
            }
            
            if ($input['name'] == 'time_from') {
                $datas['time_from'] = $input['value'];
            }
            if ($input['name'] == 'end_date') {
                $datas['end_date'] = $input['value'];
            }
            
            if ($input['name'] == 'time_to') {
                $datas['time_to'] = $input['value'];
            }
            
            if ($input['name'] == 'color') {
                $datas['color'] = $input['value'];
            }
            if ($input['name'] == 'amount') {
                $datas['amount'] = $input['value'];
            }
            if ($input['name'] == 'budget_id') {
                $datas['budget_id'] = $input['value'];
            }
            if ($input['name'] == 'project_id') {
                $datas['project_id'] = $input['value'];
            }
        }

        foreach ($dataInput as $item) {
            switch ($item['name']) {
                case 'product_id[]':
                    $product_id[] = $item['value'];
                    break;
                case 'unit_id[]':
                    $unit_id[] = $item['value'];
                    break;
                case 'budget_item_id[]':
                    $budget_item_id[] = $item['value'];
                    break;
                case 'qty[]':
                    $qty[] = $item['value'];
                    break;
                case 'price[]':
                    $price[] = $item['value'];
                    break;
            }
        }
        $employees = array_filter($dataInput, function ($item) {
            return $item['name'] === 'employees[]';
        });
        
        // Collect the `value` field
        $employeeIds = array_map(function ($item) {
            return $item['value'];
        }, $employees);

        $rowData = [];
        // Ensure all arrays have the same length
        $itemCount = count($product_id); // Assume all arrays are the same length
        // dd($itemCount);
        for ($i = 0; $i < $itemCount; $i++) {
            // Prepare data for each row
            $rowData[] = [
                'product_id' => $product_id[$i] ?? '0',
                'unit_id' => $unit_id[$i] ?? null,
                'budget_item_id' => $budget_item_id[$i] ?? null,
                'qty' => $qty[$i] ?? '0',
                'price' => $price[$i] ?? '0',
            ];
        }
        $data = $datas;
        $data_items = $rowData;
        // dd($datas);
        try {
            DB::beginTransaction();
            $data = array_replace($data, [
                'start_date' => datetime_for_database("{$data['start_date']} {$data['time_from']}"),
                'end_date' => datetime_for_database("{$data['end_date']} {$data['time_to']}"),
                'note' => $data['description'],
                'amount' => numberClean($data['amount']),
                'balance' => numberClean($data['amount']),
            ]);
            unset($data['time_from'], $data['time_to'], $data['description']);
            $project = Project::findOrFail($data['project_id']);

            if (
                $data['start_date'] < $project->start_date ||
                $data['end_date'] > $project->end_date
            ) {
                session()->flash('error', 'Milestone start and end date must be within the project start and end dates'.$project->start_date .' '.$project->end_date);
                return response()->json([
                    'error' => 'Milestone start and end date must be within the project start and end dates.'.$project->start_date .' '.$project->end_date
                ]);
            }
            // dd($data);
            $milestone =( new ProjectMileStone)->fill($data);
            $milestone->save();
            ProjectRelations::create(['project_id' => $milestone->project_id, 'milestone_id' => $milestone->id]);
            $employees = @$employeeIds ?: [];
            $employees_group = array_map(fn($v) => ['user_id' => $v,'milestone_id' => $milestone->id, 'project_id' => $milestone->project_id], $employees);
            ProjectRelations::insert($employees_group);

            // log
            $data = ['project_id' => $milestone->project_id, 'value' => '['. trans('projects.milestone') .']' .'['. trans('general.new') .'] '. $input['name'], 'user_id' => auth()->user()->id];
            ProjectLog::create($data);
            foreach ($data_items as $data_item){
                $data_item['milestone_id'] = $milestone->id;
                MileStoneItem::create($data_item);
                $budget_item = BudgetItem::find($data_item['budget_item_id']);
                $budget_item->qty_allocated_to_milestones += $data_item['qty'];
                $budget_item->update();
            }
            if($milestone){
                $project = Project::find($data['project_id']); 
                updateCompletionPercentage($milestone, $project);
                DB::commit();
            }

        } catch (\Throwable $th) {
            session()->flash('error', 'Error Creating milestone'.$th->getMessage());
            return response()->json(['error'=>'Error Creating milestone'.$th->getMessage()]);
        }
        session()->flash('success', 'Milestone Updated Successfully!');
        return response()->json(['success'=>'Milestone Created Successfully','redirect'=> route('biller.projects.show',['project'=>$milestone->project])]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \App\Http\Responses\Focus\milestone\EditResponse
     */
    public function edit(ProjectMileStone $milestone)
    {
        $project = Project::with(['quotes.budget'])->find($milestone->project_id);
        // Get the budgets associated with the project's quotes
        $budgets = $project->quotes->map(function ($quote) {
            return $quote->budget;  // Get the budget for each quote
        });
        $budget = Budget::with('items')->find($milestone->budget_id);
        $budget_items = $budget->items()
        ->whereHas('product', function ($q) { 
            $q->whereHas('product', function ($q2) {
                $q2->where('stock_type', '!=', 'service');
            });
        })
        ->where('a_type',1)
        ->with('milestone_items')
        ->get();
       $budget_items = $budget_items->filter(function ($item) {
            $allocated_qty = $item->milestone_items->sum('qty');
            $remaining_qty = $item->new_qty - $allocated_qty;
            $item->remaining_qty = $remaining_qty; // optionally attach it
            return $remaining_qty > 0;
        })->map(function ($v) {
            $v->product_name = ($v->price == 1) ? $v->product_name : ($v->product ? $v->product->name : '');
            $v->unit = $v->product ? optional($v->product->product->unit)->code : '';
            $v->unit_id = $v->product ? optional($v->product->product->unit)->id : '';
            $v->qty_allocated_to_milestones = $v->milestone_items->sum('qty');
            $v->new_qty = $v->new_qty;
            $v->remaining_qty = $v->new_qty - $v->qty_allocated_to_milestones;
            return $v;
        });
        $employees = Hrm::where('ins', auth()->user()->ins)->get();
        return view('focus.projects.milestones.edit',compact('milestone', 'project', 'budgets','employees','budget_items'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(Request $request, ProjectMileStone $milestone)
    {
        $dataInput = $request->input('data', []);


        // dd($milestone);
        $unit_id = [];
        $product_id = [];
        $qty = [];
        $budget_item_id = [];
        $price = [];
        $id = [];

        $datas = [];
        foreach($dataInput as $input){
            
            if ($input['name'] == 'name') {
                $datas['name'] = $input['value'];
            }
            
            if ($input['name'] == 'description') {
                $datas['description'] = $input['value'];
            }
            
            if ($input['name'] == 'start_date') {
                $datas['start_date'] = $input['value'];
            }
            
            if ($input['name'] == 'time_from') {
                $datas['time_from'] = $input['value'];
            }
            if ($input['name'] == 'end_date') {
                $datas['end_date'] = $input['value'];
            }
            
            if ($input['name'] == 'time_to') {
                $datas['time_to'] = $input['value'];
            }
            
            if ($input['name'] == 'color') {
                $datas['color'] = $input['value'];
            }
            if ($input['name'] == 'amount') {
                $datas['amount'] = $input['value'];
            }
            if ($input['name'] == 'budget_id') {
                $datas['budget_id'] = $input['value'];
            }
            if ($input['name'] == 'project_id') {
                $datas['project_id'] = $input['value'];
            }
        }

        foreach ($dataInput as $item) {
            switch ($item['name']) {
                case 'product_id[]':
                    $product_id[] = $item['value'];
                    break;
                case 'unit_id[]':
                    $unit_id[] = $item['value'];
                    break;
                case 'budget_item_id[]':
                    $budget_item_id[] = $item['value'];
                    break;
                case 'qty[]':
                    $qty[] = $item['value'];
                    break;
                case 'price[]':
                    $price[] = $item['value'];
                    break;
                case 'id[]':
                    $id[] = $item['value'];
                    break;
            }
        }

        $employees = array_filter($dataInput, function ($item) {
            return $item['name'] === 'employees[]';
        });
        
        // Collect the `value` field
        $employeeIds = array_map(function ($item) {
            return $item['value'];
        }, $employees);

        $rowData = [];
        // Ensure all arrays have the same length
        $itemCount = count($product_id); // Assume all arrays are the same length
        // dd($itemCount);
        for ($i = 0; $i < $itemCount; $i++) {
            // Prepare data for each row
            $rowData[] = [
                'product_id' => $product_id[$i] ?? '0',
                'unit_id' => $unit_id[$i] ?? null,
                'budget_item_id' => $budget_item_id[$i] ?? null,
                'qty' => $qty[$i] ?? '0',
                'price' => $price[$i] ?? '0',
                'id' => $id[$i] ?? '0',
            ];
        }
        $data = $datas;
        $data_items = $rowData;
        try {
            DB::beginTransaction();

            if(count($milestone->items) > 0){
                foreach($milestone->items as $item){
                    $budget_item = BudgetItem::find($item->budget_item_id);
                    $budget_item->qty_allocated_to_milestones -= $item['qty'];
                    $budget_item->update();
                }
            }

            // update milestone
            $data = array_replace($data, [
                'start_date' => datetime_for_database("{$data['start_date']} {$data['time_from']}"),
                'end_date' => datetime_for_database("{$data['end_date']} {$data['time_to']}"),
                'note' => $data['description'],
                'amount' => numberClean($data['amount']),
            ]);
            unset($data['time_from'], $data['time_to'], $data['description']);
            $project = Project::findOrFail($data['project_id']);

            if (
                $data['start_date'] < $project->start_date ||
                $data['end_date'] > $project->end_date
            ) {
                session()->flash('error', 'Milestone start and end date must be within the project start and end dates');
                return response()->json([
                    'error' => 'Milestone start and end date must be within the project start and end dates.'
                ]);
            }
            $milestone->update($data);

            // update milestone balance
            $budgetExpensesPerLine = PurchaseItem::where('budget_line_id', $milestone->id)
                ->groupBy('budget_line_id')
                ->selectRaw('budget_line_id, SUM(qty*rate*(1+itemtax*0.01)) amount')
                ->pluck('amount', 'budget_line_id');
            if (count($budgetExpensesPerLine)) {
                foreach ($budgetExpensesPerLine as $key => $amount) {
                    $project_milestone = ProjectMileStone::where('id', $key)->first();
                    if ($project_milestone) $project_milestone->update(['balance' => $project_milestone->amount-$amount]);            
                }
            } elseif ($milestone) {
                $budgetExpense = PurchaseItem::whereHas('purchase', fn($q) => $q->where('project_milestone', $milestone->id))
                ->sum(DB::raw('qty*rate*(1+itemtax*0.01)'));
                $milestone->update(['balance' => $milestone->amount-$budgetExpense]);
            }

            ProjectRelations::create(['project_id' => $milestone->project_id, 'milestone_id' => $milestone->id]);

            // log
            $data = ['project_id' => $milestone->project_id, 'value' => '['. trans('projects.milestone') .']' .'['. trans('general.new') .'] '. $input['name'], 'user_id' => auth()->user()->id];
            ProjectLog::create($data);
            $item_ids = array_map(function ($v) { return $v['id']; }, $data_items);
            $milestone->items()->whereNotIn('id', $item_ids)->delete();
            //Assign users to milestone
            $employees = @$employeeIds ?: [];
            ProjectRelations::whereNotIn('user_id', $employees)->where('project_id', $milestone->project_id)
            ->where('milestone_id',$milestone->id)
            ->whereNotNull('user_id')->whereNull('task_id')->delete();
            $employees_group = array_map(fn($v) => ['user_id' => $v,'milestone_id' => $milestone->id, 'project_id' => $milestone->project_id], $employees);
            ProjectRelations::insert($employees_group);   
    
            // create or update items
            foreach($data_items as $item) {
                foreach ($item as $key => $val) {
                    if (in_array($key, ['qty']))
                        $item[$key] = floatval(str_replace(',', '', $val));
                }
                $milestone_item = MileStoneItem::firstOrNew(['id' => $item['id']]);
                $milestone_item->fill(array_replace($item, ['milestone_id' => $milestone['id']]));
                if (!$milestone_item->id) unset($milestone_item->id);
                $milestone_item->save();
                $budget_item = BudgetItem::find($item['budget_item_id']);
                // dd($budget_item, $item['qty']);
                $budget_item->qty_allocated_to_milestones += $item['qty'];
                $budget_item->update();
            }
    
            if($milestone){
                $project = Project::find($data['project_id']); 
                updateCompletionPercentage($milestone, $project);
                DB::commit();
            }
        } catch (\Throwable $th) {
            //throw $th;
            session()->flash('error', 'Error Updating milestone'.$th->getMessage());
            return response()->json(['error'=>'Error Updating milestone'.$th->getMessage()]);
        }
        session()->flash('success', 'Milestone Updated Successfully!');
        return response()->json(['success'=>'Milestone Updated Successfully','redirect'=> route('biller.projects.show',['project'=>$milestone->project])]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeletemilestoneRequestNamespace $request
     * @param App\Models\milestone\milestone $milestone
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy()
    {
     
        //returning with successfull message
        return new RedirectResponse(route('biller.milestones.index'), ['flash_success' => trans('alerts.backend.milestones.deleted')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(ProjectMileStone $milestone)
    {

        //returning with successfull message
        return new ViewResponse('focus.projects.milestones.view', compact('milestone'));
    }

    public function get_budget_items(Request $request)
    {
        $budget = Budget::with('items')->find($request->budget_id);
        // dd($request->budget_id, $budget);
        $budget_items = $budget->items()
        ->whereHas('product', function ($q) { 
            $q->whereHas('product', function ($q2) {
                $q2->where('stock_type', '!=', 'service');
            });
        })
        ->where('a_type',1)->get();
        $budget_items = $budget_items
        // filter(function ($v) {
        //     // Assuming 'type' is a property of product that indicates the item's type
        //     return $v->product && $v->product->product->stock_type !== 'service';
        // })
        ->map(function ($v){
            // dd($v);
            // $v->product_name = $v->product ? $v->product->name : '';
            $v->product_name = ($v->price == 1) ? $v->product_name : ($v->product ? $v->product->name : '');
            $v->unit = $v->product ? @$v->product->product->unit->code : '';
            $v->unit_id = $v->product ? @$v->product->product->unit->id : '';
            $v->new_qty = $v->new_qty;
            $v->qty_allocated_to_milestones = $v->qty_allocated_to_milestones;
            return $v;
        });
        // dd($budget_items);
        return response()->json($budget_items);
    }

    public function get_milestone_items(Request $request)
    {
        if($request->milestone_id == 0){
            // dd($request->project_id);
            $project = Project::find(request('project_id'));
            $budget = optional($project->quote)->budget;

            if (!$budget) {
                return response()->json([], 200); // Return empty if no budget
            }
        
            // Query BudgetItems with a left join to MilestoneItems
            $budgetItems = BudgetItem::where('budget_id', $budget->id)
                ->whereHas('product', function ($q){
                    $q->whereHas('product', fn($q) => $q->where('stock_type', '!=', 'service'));
                })
                ->leftJoin('milestone_items', 'budget_items.id', '=', 'milestone_items.budget_item_id')
                ->select(
                    'budget_items.id',
                    'budget_items.product_id',
                    'budget_items.product_name',
                    'budget_items.price',
                    'budget_items.new_qty',
                    'budget_items.qty_requested',
                    DB::raw('COALESCE(SUM(rose_milestone_items.qty), 0) as allocated_qty')
                )
                ->groupBy('budget_items.id', 'budget_items.product_id', 'budget_items.new_qty','budget_items.qty_requested')
                ->having('budget_items.new_qty', '>', DB::raw('COALESCE(SUM(rose_milestone_items.qty), 0)'))
                ->having('budget_items.new_qty', '>', 'budget_items.qty_requested')
                ->get();
    
            // Calculate unallocated quantity
            foreach ($budgetItems as $item) {
                $item->unallocated_qty = $item->new_qty - $item->allocated_qty;
                if($item->unallocated_qty != 0){
                    $item->product_name = ($item->price == 1) ? $item->product_name : ($item->product ? $item->product->name : '');
                    $item->code = $item->product ? $item->product->code : '';
                    $item->price = $item->product ? $item->product->purchase_price : 0;
                    $item->uom = $item->product ? @$item->product->product->unit->code : '';
                    $item->unit_id = $item->product ? @$item->product->product->unit->id : '';
                    $item->qty = $item->new_qty - $item->allocated_qty;
                    $item->milestone_item_id = 0;
                    $item->budget_item_id = $item->id;
                    $item->qty_requested = $item->qty_requested;
                    $item->qty_remaining = $item->qty - $item->qty_requested;
                }
                
            }
            return response()->json($budgetItems);
        }
        $milestone = ProjectMileStone::find($request->milestone_id);
        $items = $milestone->items()->whereColumn('qty_requested','<','qty')->get();
        $items = $items->map(function($v){
            $v->product_name = @$v->product_variation->name;
            $v->code = @$v->product_variation->code;
            $v->price = @$v->product_variation->purchase_price;
            $v->qty = $v->qty;
            $v->available_qty = @$v->product_variation->qty;
            $v->uom = $v->unit_of_measure->code;
            $v->milestone_item_id = $v->id;
            $v->budget_item_id = $v->budget_item_id;
            $v->qty_requested = $v->qty_requested;
            $v->qty_remaining = $v->qty - $v->qty_requested;
            return $v;
        });
        return response()->json($items);
    }

    public function get_requisitions(Request $request)
    {
        $quote = Quote::find($request->quote_id);
        $project = $quote->project;
        
        if ($request->milestone_id > 0){
            $purchase_requests = PurchaseRequest::where('project_id', $project->id)->where('project_milestone_id', $request->milestone_id)->get();
        }else{
            $purchase_requests = PurchaseRequest::where('project_id', $project->id)->where('project_milestone_id', $request->milestone_id)->get();
        }
        $purchase_requests = $purchase_requests->map(function ($v){
            $v->name = gen4tid('REQ-',$v->tid) . '-' . $v->note;
            return $v;
        });
        return response()->json($purchase_requests);
    }

    public function milestone_unallocated_items()
    {
        // Query BudgetItems with a left join to MilestoneItems
        $budgetItems = BudgetItem::whereHas('budget', function($q) {
            $q->whereHas('quote', fn($q) => $q->whereHas('project', fn($q) => $q->where('projects.id', request('project_id'))));
        })
        ->whereHas('product', function ($q) {
            $q->whereHas('product', function ($q) {
                $q->where('stock_type', '!=', 'service');
            });
        })
        ->leftJoin('milestone_items', 'budget_items.id', '=', 'milestone_items.budget_item_id')
        ->select(
            'budget_items.id',
            'budget_items.product_name',
            'budget_items.price',
            'budget_items.product_id',
            'budget_items.new_qty',
            DB::raw('COALESCE(SUM(rose_milestone_items.qty), 0) as allocated_qty')
        )
        ->groupBy('budget_items.id', 'budget_items.product_id', 'budget_items.new_qty')
        ->get();

        // Calculate unallocated quantity
        foreach ($budgetItems as $item) {
            $item->name = ($item->price == 1) ? $item->product_name : ($item->product ? $item->product->name : '');
            $item->code = $item->product ? $item->product->code : '';
            $item->purchase_price = $item->product ? (fifoCost($item->product->id) ?: $item->product->purchase_price) : 0;
            $item->price = $item->product ? $item->product->price : 0;
            $item->difference = $item->price - $item->purchase_price;
            $item->percentage = div_num($item->difference, $item->purchase_price) * 100;
            $item->unit = $item->product ? $item->product->product->unit : '';
            $item->unallocated_qty = $item->new_qty - $item->allocated_qty;
            $item->allocated_qty = $item->allocated_qty;
        }

        // Return DataTables response
        return DataTables::of($budgetItems)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('name', function ($item) {
                return $item->name;
            })
            ->addColumn('code', function ($item) {
                return $item->code;
            })
            ->addColumn('uom', function ($item) {
                return $item->unit ? $item->unit->code : '';
            })
            ->addColumn('allocated_qty', function ($item) {
                return numberFormat($item->allocated_qty);
            })
            ->addColumn('allocated_qty', function ($item) {
                return numberFormat($item->allocated_qty);
            })
            ->addColumn('purchase_price', function ($item) {
                return numberFormat($item->purchase_price);
            })
            ->addColumn('selling_price', function ($item) {
                return numberFormat($item->price);
            })
            ->addColumn('difference', function ($item) {
                return numberFormat($item->difference);
            })
            ->addColumn('percentage', function ($item) {
                return numberFormat($item->percentage);
            })
            ->addColumn('qty', function ($item) {
                $status = numberFormat($item->unallocated_qty);
                if ($item->unallocated_qty == 0) {
                    $status = '<span style="color: red;"><b>' . numberFormat($item->unallocated_qty) . '</b></span>';
                }
                return $status;
            })
            ->make(true);

    }

    public function items()
    {
        $milestone = ProjectMilestone::find(request('milestone_id'));

        if (!$milestone) {
            return response()->json(['error' => 'Milestone not found'], 404);
        }

        $items = $milestone->items()->get();

        foreach ($items as $item) {
            $budget = $item->budget_item->budget ?? null;
            $quote_id = $budget ? $budget->quote_id : null;

            // Fetch the quote item and quote selling price
            $quote_item = $quote_id ? QuoteItem::where(['quote_id' => $quote_id, 'product_id' => $item->product_id])->first() : null;
            $quote_selling_price = $quote_item->product_subtotal ?? 0;

            // Assign values to item properties
            $item->name = ($item->price == 1) ? $item->product_name : ($item->product_variation->name ?? '');
            $item->code = $item->product_variation->code ?? '';
            $item->purchase_price = $item->product_variation 
                ? (fifoCost($item->product_variation->id) ?: $item->product_variation->purchase_price) 
                : 0;

            $item->price = $quote_selling_price > 0 
                ? $quote_selling_price 
                : ($item->product_variation && $item->product_variation->price > 0 
                    ? $item->product_variation->price 
                    : 0);

            $item->difference = $item->price - $item->purchase_price;
            $item->percentage = $item->purchase_price > 0 
                ? div_num($item->difference, $item->purchase_price) * 100 
                : 0;

            $item->uom = $item->unit_of_measure->code ?? '';
            $item->unallocated_qty = $item->budget_item->new_qty - $item->qty;
            $item->allocated_qty = $item->qty;
        }

        return response()->json($items);
    }


}
