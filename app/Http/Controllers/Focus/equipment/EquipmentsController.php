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

namespace App\Http\Controllers\Focus\equipment;

use App\Models\equipment\Equipment;
use App\Models\items\ContractServiceItem;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\equipment\CreateResponse;
use App\Http\Responses\Focus\equipment\EditResponse;
use App\Repositories\Focus\equipment\EquipmentRepository;
use App\Http\Requests\Focus\equipment\ManageEquipmentRequest;
use App\Http\Requests\Focus\equipment\StoreEquipmentRequest;
use App\Models\branch\Branch;
use App\Models\customer\Customer;
use App\Models\equipmenttoolkit\EquipmentToolKit;
use App\Models\djc\Djc;
use App\Models\rjc\Rjc;
use App\Models\quote\Quote;
use App\Models\contractservice\ContractService;
use App\Models\verifiedjcs\VerifiedJc;
use Illuminate\Support\Facades\DB;

/**
 * ProductcategoriesController
 */
class EquipmentsController extends Controller
{
    /**
     * variable to store the repository object
     * @var ProductcategoryRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param ProductcategoryRepository $repository ;
     */
    public function __construct(EquipmentRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\productcategory\ManageProductcategoryRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index(ManageEquipmentRequest $request)
    {
        $customerLoginId = auth()->user()->customer_id;
        $customers = Customer::when($customerLoginId, fn($q) => $q->where('id', $customerLoginId))
            ->get(['id', 'company']);
        $branches = Branch::whereIn('customer_id', $customers->pluck('id')->toArray())
        ->where('name', '!=', 'All Branches')
        ->get(['id', 'name', 'customer_id']);

       return new ViewResponse('focus.equipments.index', compact('customers', 'branches'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\Focus\productcategory\CreateResponse
     */
    public function create(StoreEquipmentRequest $request)
    {
        return new CreateResponse('focus.equipments.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(StoreEquipmentRequest $request)
    {
        $this->repository->create($request->except('_token'));

        return new RedirectResponse(route('biller.equipments.index'), ['flash_success' => 'Equipment Created Successfully']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\productcategory\Productcategory $productcategory
     * @param EditProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\Focus\productcategory\EditResponse
     */
    public function edit(Equipment $equipment)
    {
        return new EditResponse($equipment);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateProductcategoryRequestNamespace $request
     * @param App\Models\productcategory\Productcategory $productcategory
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(StoreEquipmentRequest $request, Equipment $equipment)
    {
        $this->repository->update($equipment, $request->except('_token'));

        return new RedirectResponse(route('biller.equipments.index'), ['flash_success' => 'Equipment  Updated Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteProductcategoryRequestNamespace $request
     * @param App\Models\productcategory\Productcategory $productcategory
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(Equipment $equipment)
    {

        $this->repository->delete($equipment);

        return new RedirectResponse(route('biller.equipments.index'), ['flash_success' => 'Equipment Deleted Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteProductcategoryRequestNamespace $request
     * @param App\Models\productcategory\Productcategory $productcategory
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(Equipment $equipment)
    {
        $group = $this->equipment_report($equipment->id);
        $grouped = $group['grouped'];
        $customer = $group['customer'];
        $branch = $group['branch'];
        return new ViewResponse('focus.equipments.view', compact('equipment', 'grouped','customer','branch'));
    }

    /**
     * Fetch customer equipments
     */
    public function equipment_search(Request $request)
    {
        $k = $request->post('keyword');
        
        $equipments = Equipment::when(request('branch_id'), function ($q) {
            $q->where('branch_id', request('branch_id'));
        })->when(request('customer_id'), function ($q) {
            $q->where('customer_id', request('customer_id'));
        })->when(request('schedule_id'), function ($q) {
            // unserviced equipments
            $q->whereHas('contract_equipments', function ($q) {
                $q->where('schedule_id', request('schedule_id'));
            })->where(function ($q) {
                $q->doesntHave('contract_service_items', 'or', function ($q) {
                    $q->whereHas('contractservice', function ($q) {
                        $q->where('schedule_id', request('schedule_id'));
                    });
                });
            });
        })
        ->where(function ($q) use($k) {
            $q->where('tid', 'LIKE', '%' . $k . '%')
            ->orWhere('make_type', 'LIKE', '%' . $k . '%')
            ->orWhere('location', 'LIKE', '%' . $k . '%');
        })->limit(10)->get();
        foreach ($equipments as $equipment) {
            $equipment->tid = gen4tid('Eq-',$equipment->tid);
        }

        return response()->json($equipments);
    }

    // 
    public function equipment_load()
    {
        $equipments = array();
        if (request('id') != 1) 
            $equipments = Equipment::get();
        
        return response()->json($equipments);
    }
    
     public function attach(Request $request)
    {
        if(EquipmentToolkit::where('equipment_id',$request->equipment_id)->where('tool_id',$request->toolkit_id)->exists()){
            return new RedirectResponse(route('biller.equipments.show',$request->equipment_id), ['flash_success' => 'ToolKit Already Attached']);
        }
        $equipment_toolkit = new EquipmentToolkit();
        $equipment_toolkit->equipment_id = $request->equipment_id;
        $equipment_toolkit->tool_id = $request->toolkit_id;
        $equipment_toolkit['ins'] = auth()->user()->ins;
        $equipment_toolkit['user_id'] = auth()->user()->id;
        $equipment_toolkit->save();
        return new RedirectResponse(route('biller.equipments.show',$request->equipment_id), ['flash_success' => 'ToolKit Attached Successfully']);
    }
    public function dettach(Request $request)
    {
        // dd($request->all());
        $dettach_equipment = EquipmentToolkit::where('equipment_id',$request->equipment_id)->where('tool_id',$request->toolkit_name)->get()->first();
        $dettach_equipment->delete();
        return new RedirectResponse(route('biller.equipments.show',$request->equipment_id), ['flash_success' => 'ToolKit Dettached Successfully']);
    }
     public function equipment_report($equipment_id){
        
        $results = DB::select("
            SELECT rose_equipments.tid AS equip_tid,rose_equipments.customer_id AS customer_id, rose_equipments.branch_id AS branch_id, 
            rose_djc_item.djc_id AS djc_id, 
            rose_quote_equipment.quote_id AS quotation_id,rose_quote_equipment.fault AS faulting,
            rose_verified_jcs.fault AS faults,rose_verified_jcs.quote_id AS verify_quote_id,
            rose_rjc_items.rjc_id AS rjc_id,
            rose_contract_service_items.*
            FROM rose_equipments
            LEFT JOIN rose_djc_item ON rose_equipments.id = rose_djc_item.equipment_id
            LEFT JOIN rose_quote_equipment ON rose_equipments.id = rose_quote_equipment.item_id
            LEFT JOIN rose_verified_jcs ON rose_equipments.id = rose_verified_jcs.equipment_id
            LEFT JOIN rose_rjc_items ON rose_equipments.id = rose_rjc_items.equipment_id
            LEFT JOIN rose_contract_service_items ON rose_equipments.id = rose_contract_service_items.equipment_id
            WHERE rose_equipments.id = ".$equipment_id."
            AND (
                rose_djc_item.id IS NOT NULL
                OR rose_quote_equipment.id IS NOT NULL
                OR rose_verified_jcs.id IS NOT NULL
                OR rose_rjc_items.id IS NOT NULL
                OR rose_contract_service_items.id IS NOT NULL
            )
    
        ");
        $djc = [];
        $rjc = [];
        $quote_equipment = [];
        $schedule = [];
        $v_quote = [];
        $customer = '';
        $branch = '';
        foreach ($results as $result) {
            $customer = Customer::find($result->customer_id)->name;
            $branch = Branch::find($result->branch_id)->name;

            $djc_item = Djc::find($result->djc_id);
            if (is_object($djc_item)) {
                $djc = [
                    'tid' => gen4tid('DJR-',$djc_item->tid),
                    'fault'=> strip_tags($djc_item->root_cause),
                    'date'=> $djc_item->report_date ? dateFormat($djc_item->report_date) : '',
                    'document_type' => 'DJC REPORT',
                ];
            } else {
                $djc = [
                    'tid' => '',
                    'fault'=> '',
                    'date'=> '',
                    'document_type' => '',
                ];
            }
            
            $rjc_item = Rjc::find($result->rjc_id);
            if (is_object($rjc_item)) {
                $rjc = [
                    'tid' => gen4tid('RJR-',$rjc_item->tid),
                    'fault'=> strip_tags($rjc_item->action_taken),
                    'date'=> $rjc_item->report_date ? dateFormat($rjc_item->report_date):'',
                    'document_type' => 'RJC REPORT',
                    
                ];
            } else {
                $rjc = [
                    'tid' => '',
                    'fault'=> '',
                    'date'=> '',
                    'document_type' => '',
                ];
            }
            
            $quote = Quote::find($result->quotation_id);
            
            if (is_object($quote)) {
                $equip_quote = $quote->equipments ? $quote->equipments->first() : '';
                $quote_equipment = [
                    'tid' => gen4tid('QT-',$quote->tid),
                    'fault'=> $result->faulting,
                    'date'=> $quote->date? dateFormat($quote->date): '',
                    'document_type' => 'QUOTE REPORT',
                ];
            } else {
                $quote_equipment = [
                    'tid' => '',
                    'fault'=> '',
                    'date'=> '',
                    'document_type' => '',
                ];
            }
            

        
            $verified_quote = Quote::find($result->verify_quote_id);
            if (is_object($verified_quote)) {
                $v_quote = [
                    'tid' => gen4tid('QT-',$verified_quote->tid).'-'.'v',
                    'fault'=> $result->faults,
                    'date'=> $verified_quote->date ? dateFormat($verified_quote->date): '',
                    'document_type' => 'VERIFICATION REPORT',
                ];
            } else {
                $v_quote = [
                    'tid' => '',
                    'fault'=> '',
                    'date'=> '',
                    'document_type' => '',
                ];
            }
            

            $contract_service = ContractService::find($result->contractservice_id);
            $schedules = $contract_service->task_schedule? $contract_service->task_schedule->first() : null;
            if (is_object($verified_quote)) {
                $schedule = [
                    'tid' => $schedules->title,
                    'fault'=> $result->status,
                    'date'=> $contract_service->date ? dateFormat($contract_service->date) :'',
                    'document_type' => 'SCHEDULE REPORT',
                ];
            } else {
                $schedule = [
                    'tid' => '',
                    'fault'=> '',
                    'date'=> '',
                    'document_type' => '',
                ];
            }
            
        }
        
        $grouped = [
            $schedule,$rjc, $v_quote,$quote_equipment, $djc
        ];
       return compact('grouped', 'customer','branch');
    }
}
