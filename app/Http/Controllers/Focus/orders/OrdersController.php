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
namespace App\Http\Controllers\Focus\orders;

use App\Models\department\Department;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\additional\Additional;
use App\Models\customer\Customer;
use App\Models\hrm\Hrm;
use App\Models\orders\Orders;
use App\Models\target_zone\TargetZoneItem;
use App\Repositories\Focus\orders\OrdersRepository;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

/**
 * OrdersController
 */
class OrdersController extends Controller
{
    /**
     * variable to store the repository object
     * @var OrdersRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param OrdersRepository $repository ;
     */
    public function __construct(OrdersRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \App\Http\Responses\ViewResponse
     */
    public function index()
    {
        return new ViewResponse('focus.customer_orders.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateDepartmentRequestNamespace $request
     * @return \App\Http\Responses\Focus\department\CreateResponse
     */
    public function create()
    {
        $last_tid = Orders::max('tid');
        $customers = Customer::all();
        $additionals = Additional::all();
        $users = Hrm::all();
        $locations = TargetZoneItem::with('target_zone')->get();
        return view('focus.customer_orders.create', compact('last_tid','customers','additionals','users','locations'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreDepartmentRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(Request $request)
    {
        // dd($request->all());
        //Input received from the request
        $data = $request->only([
            'tid','customer_id','branch_id','order_type','description',
            'frequency','subtotal','total','tax','taxable',
            'start_month','end_month','driver_id','route'
        ]);
        // $data['expected_time'] = Carbon::parse($data['expected_time'])->format('H:i:s');
        $days = $request->only(['delivery_days','expected_time']);
        $data_items = $request->only(['product_id','qty','type','rate','tax_rate','itemtax','amount']);
        $data_items = modify_array($data_items);
        $days = modify_array($days);
        dd($data_items);
        try {
            //Create the model using repository create method
            $this->repository->create(compact('data','data_items','days'));
        } catch (\Throwable $th) {dd($th);
            //throw $th
            return errorHandler('Error Creating Customer Order',$th);
        }
        //return with successfull message
        return new RedirectResponse(route('biller.customer_orders.index'), ['flash_success' => 'Order Created Successfully!!']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\department\Department $department
     * @param EditDepartmentRequestNamespace $request
     * @return \App\Http\Responses\Focus\department\EditResponse
     */
    public function edit(Orders $customer_order)
    {
        $last_tid = Orders::max('tid');
        $customers = Customer::all();
        $additionals = Additional::all();
        $users = Hrm::all();
        $locations = TargetZoneItem::with('target_zone')->get();
        return view('focus.customer_orders.edit', compact('customer_order','last_tid','customers','additionals','users','locations'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateDepartmentRequestNamespace $request
     * @param App\Models\department\Department $department
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(Request $request, $order_id)
    {
        $order = Orders::find($order_id);
        $data = $request->only([
            'customer_id','branch_id','order_type','description',
            'frequency','subtotal','total','tax','taxable',
            'start_month','end_month','driver_id','route'
        ]);
        // dd($data);
        // $data['expected_time'] = Carbon::parse($data['expected_time'])->format('H:i:s');
        $data_items = $request->only(['product_id','qty','type','rate','tax_rate','itemtax','amount','id']);
        $data_items = modify_array($data_items);
        $days = $request->only(['delivery_days','expected_time','d_id']);
        $days = modify_array($days);
        if($data['order_type'] == 'recurring'){
            if(empty($days)) throw ValidationException::withMessages(['Delivery Days are not selected']);
        }
        try {
            //Update the model using repository update method
            $this->repository->update($order, compact('data','data_items','days'));
        } catch (\Throwable $th) {dd($th);
             return errorHandler('Error Updating Customer Order',$th);
        }
        //return with successfull message
        return new RedirectResponse(route('biller.customer_orders.index'), ['flash_success' => 'Order Updated Successfully!!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteDepartmentRequestNamespace $request
     * @param App\Models\department\Department $department
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(Orders $orders)
    {
        //Calling the delete method on repository
        $this->repository->delete($orders);
        //returning with successfull message
        return new RedirectResponse(route('biller.customer_orders.index'), ['flash_success' => 'Order Deleted Successfully!!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteDepartmentRequestNamespace $request
     * @param App\Models\department\Department $department
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show($order_id)
    {
        $orders = Orders::find($order_id);
        //returning with successfull message
        return new ViewResponse('focus.customer_orders.view', compact('orders'));
    }

    public function select(Request $request)
    {
        $q = $request->search;
        $customer_id = $request->customer_id;

        $orders = Orders::whereIn('status',['confirmed','started'])->where('customer_id', $customer_id)
            // ->where('description', 'LIKE', '%'.$q.'%')
            ->whereHas('schedules', function($q){
                $q->whereDate('delivery_date',date('Y-m-d'));
            })
            ->limit(6)->get();
        $orders->map(function($v){
            $v->name = gen4tid('ORD-',$v->tid). '-'.$v->description;
        });
            
        return response()->json($orders);
    }
    public function search(Request $request)
    {
        $q = $request->search;
        $customer_id = $request->customer_id;

        $orders = Orders::whereIn('status',['confirmed','started','completed'])->where('customer_id', $customer_id)
            // ->where('description', 'LIKE', '%'.$q.'%')
            ->limit(6)->get();
        $orders->map(function($v){
            $v->name = gen4tid('ORD-',$v->tid). '-'.$v->description;
        });
            
        return response()->json($orders);
    }

    public function order_items(Request $request)
    {
        $order = Orders::where('id', $request->order_id)->first();
        $items = $order->items()->get();
        $items->map(function($v){
            $v->product_name = $v->product ? $v->product->name : '';
            $v->product_code = $v->product ? $v->product->code : '';
            return $v;
        });
        return response()->json($items);
    }

}
