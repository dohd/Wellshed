<?php

namespace App\Http\Controllers\Focus\taskschedule;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\branch\Branch;
use App\Models\contract\Contract;
use App\Models\customer\Customer;
use App\Models\task_schedule\TaskSchedule;
use App\Repositories\Focus\taskschedule\TaskScheduleRepository;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TaskSchedulesController extends Controller
{
    /**
     * variable to store the repository object
     * @var TaskScheduleRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param TaskScheduleRepository $repository ;
     */
    public function __construct(TaskScheduleRepository $repository)
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
        $customerLoginId = auth()->user()->customer_id;
        $customers = Customer::when($customerLoginId, fn($q) => $q->where('id', $customerLoginId))
            ->get(['id', 'company']);
        $contracts = Contract::whereIn('customer_id', $customers->pluck('id')->toArray())
            ->get(['id', 'title', 'customer_id']);

        return new ViewResponse('focus.taskschedules.index', compact('customers', 'contracts'));        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $contracts = Contract::all();

        return new ViewResponse('focus.taskschedules.create', compact('contracts'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'contract_id' => 'required',
            'schedule_id' => 'required',
        ]);

        // extract request input
        $data = $request->only(['contract_id', 'schedule_id' , 'actual_startdate', 'actual_enddate']);
        $data_items = array_map(fn($v) => ['equipment_id' => $v], explode(',', request('equipment_ids')));
        if (!$data_items) throw ValidationException::withMessages(['equipments required!']);
    
        try {
            $this->repository->create(compact('data', 'data_items'));
        } catch (\Throwable $th) {
            return errorHandler('Error loading Task Schedule Equipments', $th);
        }
        
        return new RedirectResponse(route('biller.taskschedules.index'), ['flash_success' => 'Task Schedule Equipments loaded successfully | Schedule Calendar Event Created']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(TaskSchedule $taskschedule)
    {    
        $taskschedules_rel = TaskSchedule::where('contract_id', $taskschedule->contract_id)
            ->doesntHave('equipments')
            ->get(['id', 'title']);

        $customer_id = @$taskschedule->contract->customer_id;
        $taskschedule['equipments'] = $taskschedule->equipments()
            ->whereHas('branch', fn($q) => $q->where('customer_id', $customer_id))
            ->get();
            
        $branch_ids = $taskschedule->equipments()
            ->whereHas('branch', fn($q) => $q->where('customer_id', $customer_id))
            ->select('branch_id')->distinct()
            ->pluck('branch_id')->toArray(); 
        
        $branches = Branch::whereIn('id', $branch_ids)->with([
            'taskschedule_equipments' => fn($q) => $q->where('schedule_id', $taskschedule->id),
            'service_contract_items' => function($q) use($taskschedule) {
                $q->whereHas('contractservice', fn($q) =>  $q->where('schedule_id', $taskschedule->id));
            },
        ])->get();
            
        return new ViewResponse('focus.taskschedules.view', compact('taskschedule', 'taskschedules_rel', 'branches'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(TaskSchedule $taskschedule)
    {
        $customer_id = @$taskschedule->contract->customer_id;
        $taskschedule['equipments'] = $taskschedule->equipments()
            ->whereHas('branch', fn($q) => $q->where('customer_id', $customer_id))
            ->get();

        return new ViewResponse('focus.taskschedules.edit', compact('taskschedule'));        
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TaskSchedule $taskschedule)
    {
        $data = $request->only([
            'title', 'start_date', 'end_date', 'actual_startdate', 'actual_enddate', 'schedule_id', 
            'is_copy'
        ]);
        $data_items = array_map(fn($v) => ['id' => $v], explode(',', request('equipment_ids')));
        if (!$data_items) throw ValidationException::withMessages(['equipments required!']);

        try {
            $this->repository->update($taskschedule, compact('data', 'data_items'));
        } catch (\Throwable $th) {
            return errorHandler('Error Updating Task Schedule', $th);
        }

        return new RedirectResponse(route('biller.taskschedules.index'), ['flash_success' => 'Task Schedule updated successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(TaskSchedule $taskschedule)
    {
        try {
            $result = $this->repository->delete($taskschedule);

            $msg = ['flash_success' => 'Task Schedule deleted successfully'];
            if (!$result) $msg = ['flash_error' => 'Task Schedule has a service report!'];
        } catch (\Throwable $th) {
            return errorHandler('Error Deleting Task Schedule', $th);
        }

        return new RedirectResponse(route('biller.taskschedules.index'), $msg);
    }

    /**
     * Display dropdown liasting for Quote / PI
     */
    public function quote_product_search()
    {
        $taskschedules = TaskSchedule::whereHas('equipments')
            ->whereHas('contract', fn($q) => $q->where('customer_id', request('customer_id')))
            ->get()->map(fn($v) => [
                'id' => $v->id,
                'name' => "{$v->title} - {$v->contract->title}",
                'unit' => 'Lot',
                'purchase_price' => 0,
                'price' => $v->equipments->sum('service_rate'),
                'product_id' => $v->id,
            ]);

        return response()->json($taskschedules);
    }
}
