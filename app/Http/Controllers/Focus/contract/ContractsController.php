<?php

namespace App\Http\Controllers\Focus\contract;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\branch\Branch;
use App\Models\contract\Contract;
use App\Models\equipment\Equipment;
use App\Models\project\BudgetItem;
use App\Models\task_schedule\TaskSchedule;
use App\Repositories\Focus\contract\ContractRepository;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ContractsController extends Controller
{
    /**
     * variable to store the repository object
     * @var ContractRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param ContractRepository $repository ;
     */
    public function __construct(ContractRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return new ViewResponse('focus.contracts.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $last_tid = Contract::where('ins', auth()->user()->ins)->max('tid');

        return new ViewResponse('focus.contracts.create', compact('last_tid'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // extract input fields
        $contract_data = $request->only([
            'tid', 'customer_id', 'title', 'start_date', 'end_date', 'amount', 'period', 'schedule_period', 'note'
        ]);
        $schedule_data = $request->only('s_title', 's_start_date', 's_end_date');
        $equipment_data = $request->only('equipment_id');
        $document_data = $request->only('caption','contract_doc');

        $contract_data['ins'] = auth()->user()->ins;
        $contract_data['user_id'] = auth()->user()->id;

        $schedule_data = modify_array($schedule_data);
        $equipment_data = modify_array($equipment_data);
        $document_data = modify_array($document_data);

        try {
            $this->repository->create(compact('contract_data', 'schedule_data', 'equipment_data', 'document_data'));
        } catch (\Throwable $th) {
            if ($th instanceof ValidationException) throw $th;
            return errorHandler('Error Creating Contract', $th);
        }

        return new RedirectResponse(route('biller.contracts.index'), ['flash_success' => 'Contract created successfully']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Contract $contract)
    {
        $contract['task_schedules'] = $contract->task_schedules()->with(['equipments' => function($q) use($contract) {
            $q->whereHas('branch', fn($q) => $q->where('customer_id', $contract->customer_id));
        }])->get();
        
        $contract['equipments'] = $contract->equipments()
            ->whereHas('branch', fn($q) => $q->where('customer_id', $contract->customer_id))
            ->get();

        $branch_ids = $contract->equipments->pluck('branch_id')->toArray();
        $branches = Branch::whereIn('id', $branch_ids)->with([
            'contract_equipments' => fn($q) => $q->where('contract_id', $contract->id),
            'service_contract_items' => function($q) use($contract) {
                $q->whereHas('contractservice', fn($q) =>  $q->where('contract_id', $contract->id));
            },
        ])->get();

        return new ViewResponse('focus.contracts.view', compact('contract', 'branches'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Contract $contract)
    {
        $contract['equipments'] = $contract->equipments()
            ->whereHas('branch', fn($q) => $q->where('customer_id', $contract->customer_id))
            ->get();
            
        return new ViewResponse('focus.contracts.edit', compact('contract'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Contract $contract)
    {
        // extract input fields
        $contract_data = $request->only([
            'tid', 'customer_id', 'title', 'start_date', 'end_date', 'amount', 'period', 'schedule_period', 'note'
        ]);
        $schedule_data = $request->only('s_id', 's_title', 's_start_date', 's_end_date');
        $equipment_data = $request->only('contracteq_id', 'equipment_id');
        $document_data = $request->only('doc_id','caption','contract_doc','existing_contract_doc');

        $contract_data['ins'] = auth()->user()->ins;
        $contract_data['user_id'] = auth()->user()->id;
        
        $schedule_data = modify_array($schedule_data);
        if (!$schedule_data) throw ValidationException::withMessages(['contract schedules required!']);

        $equipment_data = modify_array($equipment_data); 
        if (!$equipment_data) throw ValidationException::withMessages(['contract equipments required!']);
        $document_data = modify_array($document_data);
            
        try {
            $this->repository->update($contract, compact('contract_data', 'schedule_data', 'equipment_data','document_data'));
        } catch (\Throwable $th) {
            if ($th instanceof ValidationException) throw $th;
            return errorHandler('Error Updating Contract', $th);
        }
        return new RedirectResponse(route('biller.contracts.index'), ['flash_success' => 'Contract edited successfully']);
    }


    /**
     * Remove resource from storage
     */
    public function destroy(Contract $contract)
    {
        try {
            $this->repository->delete($contract);
        } catch (\Throwable $th) {
            if ($th instanceof ValidationException) throw $th;
            return errorHandler('Error Deleting Contract', $th);
        }

        return new RedirectResponse(route('biller.contracts.index'), ['flash_success' => 'Contract deleted successfully']);
    }

    /**
     * Load Additional Equipments
     */
    public function create_add_equipment()
    {
        return new ViewResponse('focus.contracts.create_add_equipment');
    }

    public function store_add_equipment(Request $request)
    {
        // extract request input
        $contract_id = $request->contract_id;
        $data_items = $request->only('equipment_id');

        $data_items = modify_array($data_items);
        $data_items = array_map(function ($v) use($contract_id) {
            return $v + compact('contract_id');
        }, $data_items);

        $this->repository->add_equipment($data_items);

        return new RedirectResponse(route('biller.contracts.index'), ['flash_success' => 'Contract Equipment Added Successfully']);
    }

    /**
     * Customer Contracts
     */
    public function customer_contracts()
    {
        $contracts = Contract::where('customer_id', request('customer_id'))->get();

        return response()->json($contracts);
    }

    /**
     * Contract task schedules
     */
    public function task_schedules()
    {
        $contract_id = request('contract_id');
        $is_report = request('is_report');

        $task_schedules = array();

        if ($is_report) {
            // fetch schedules in budgeted PI project
            $contract = Contract::find($contract_id);
            $budget_item_names = BudgetItem::where('a_type', 1)
                ->where('product_name', 'LIKE', "%{$contract->title}%")
                ->pluck('product_name')->toArray();
            $schedule_titles = array_map(function ($v) {
                return current(explode(' - ', $v));
            }, $budget_item_names);

            $task_schedules = TaskSchedule::where('contract_id', $contract_id)->whereIn('title', $schedule_titles)->get();
        } else {
            $task_schedules = TaskSchedule::where('contract_id', $contract_id)->get();
        }
        
        return response()->json($task_schedules);
    }

    /**
     * Customer equipments
     */
    public function customer_equipment()
    {
        $equipments = Equipment::when(request('branch_id'), function ($q) {
            $q->where('branch_id', request('branch_id'));
        })->where('customer_id', request('customer_id'))
        ->doesntHave('contracts')
        ->with(['branch' => fn($q) => $q->get(['id', 'name'])])
        ->get()
        ->map(fn($v) => [
            'id' => $v->id, 
            'unique_id' => $v->unique_id, 
            'equip_serial' => $v->equip_serial,
            'branch' => $v->branch, 
            'location' => $v->location, 
            'make_type' => $v->make_type
        ]);
        
        return response()->json($equipments);
    }

    /**
     * Contract equipments
     */
    public function contract_equipment()
    {
        $equipments = Equipment::doesntHave('task_schedules')
            ->whereHas('contracts', function ($q) {
                $q->where('contract_id', request('contract_id'));
            })->get()->map(function ($v) {
                $v->branch = $v->branch;
                return $v;
            });
        
        return response()->json($equipments);
    }    
}
