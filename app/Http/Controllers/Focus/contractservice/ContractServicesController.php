<?php

namespace App\Http\Controllers\Focus\contractservice;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\branch\Branch;
use App\Models\contract\Contract;
use App\Models\contractservice\ContractService;
use App\Models\customer\Customer;
use App\Models\task_schedule\TaskSchedule;
use App\Repositories\Focus\contractservice\ContractServiceRepository;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ContractServicesController extends Controller
{
    /**
     * variable to store the repository object
     * @var ContractServiceRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param ContractServiceRepository $repository ;
     */
    public function __construct(ContractServiceRepository $repository)
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
        $customers = Customer::get(['id', 'company']);
        $contracts = Contract::get(['id', 'title', 'customer_id']);
        $schedules = TaskSchedule::whereIn('id', function ($q) {
            $q->select('schedule_id')->distinct()->from('contract_services');
        })->get(['id', 'title', 'contract_id']);
        $branches = Branch::whereIn('id', function ($q) {
            $q->select('branch_id')->distinct()->from('contract_services');
        })->where('name', '!=', 'All Branches')->get(['id', 'name', 'customer_id']);
        
        return new ViewResponse('focus.contractservices.index', compact('customers', 'contracts', 'schedules', 'branches'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return new ViewResponse('focus.contractservices.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // extract request input
        $data = $request->only([
            'customer_id', 'branch_id', 'contract_id', 'schedule_id', 'date', 'jobcard_no', 'technician', 
            'rate_ttl', 'bill_ttl', 'remark'
        ]);
        $data_items = $request->only(['equipment_id', 'status', 'is_bill', 'note']);

        $data['ins'] = auth()->user()->ins;
        $data['user_id'] = auth()->user()->id;

        $data_items = modify_array($data_items);
        if (!$data_items) throw ValidationException::withMessages(['Cannot create report without equipments!']);

        $this->repository->create(compact('data', 'data_items'));

        return new RedirectResponse(route('biller.contractservices.index'), ['flash_success' => 'Contract Service Report created successfully']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(ContractService $contractservice)
    {
        return new ViewResponse('focus.contractservices.view', compact('contractservice'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(ContractService $contractservice, Request $request)
    {
        return new ViewResponse('focus.contractservices.edit', compact('contractservice'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ContractService $contractservice)
    {
        // extract request input
        $data = $request->only([
            'customer_id', 'branch_id', 'contract_id', 'schedule_id', 'date', 'jobcard_no', 
            'technician', 'rate_ttl', 'bill_ttl', 'remark'
        ]);
        $data_items = $request->only(['item_id', 'equipment_id', 'status', 'is_bill', 'note']);

        $data['ins'] = auth()->user()->ins;
        $data['user_id'] = auth()->user()->id;

        $data_items = modify_array($data_items);
        if (!$data_items) throw ValidationException::withMessages(['Cannot create report without equipments!']);

        $this->repository->update($contractservice, compact('data', 'data_items'));

        return new RedirectResponse(route('biller.contractservices.index'), ['flash_success' => 'Contract Service Report updated successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(ContractService $contractservice)
    {
        $this->repository->delete($contractservice);

        return new RedirectResponse(route('biller.contractservices.index'), ['flash_success' => 'Contract Service Report deleted successfully']);
    }

    /**
     * Display listing of serviced equipments
     */
    public function serviced_equipment()
    {
        $customers = Customer::get(['id', 'company']);
        $contracts = Contract::get(['id', 'title', 'customer_id']);
        $branches = Branch::whereIn('id', function ($q) {
            $q->select('branch_id')->distinct()->from('contract_services');
        })->where('name', '!=', 'All Branches')->get(['id', 'name', 'customer_id']);
        $schedules = TaskSchedule::whereIn('id', function ($q) {
            $q->select('schedule_id')->distinct()->from('contract_services');
        })->get(['id', 'title', 'contract_id']);
        
        return view('focus.contractservices.serviced_equipment', compact('customers', 'branches', 'contracts', 'schedules'));
    }
}
