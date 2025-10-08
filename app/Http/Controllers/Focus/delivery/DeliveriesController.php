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
namespace App\Http\Controllers\Focus\delivery;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\delivery\Delivery;
use App\Models\delivery\DeliveryItem;
use App\Models\hrm\Hrm;
use App\Models\orders\Orders;
use Illuminate\Support\Facades\DB;

/**
 * DeliveriesController
 */
class DeliveriesController extends Controller
{
    

    /**
     * Display a listing of the resource.
     *
     * @return \App\Http\Responses\ViewResponse
     */
    public function index()
    {
        return new ViewResponse('focus.deliveries.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreatedeliveryRequestNamespace $request
     * @return \App\Http\Responses\Focus\delivery\CreateResponse
     */
    public function create()
    {
        $orders = Orders::whereIn('status',['confirmed','started'])->get();
        $users = Hrm::all();
        return view('focus.deliveries.create', compact('orders','users'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoredeliveryRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(Request $request)
    {
        // dd($request->all());
        //Input received from the request
        $data = $request->only(['order_id','delivery_schedule_id','date','driver_id','description']);
        $data_items = $request->only('product_id','planned_qty','delivered_qty','returned_qty');
        $data_items = modify_array($data_items);
        $data_items = array_filter($data_items, fn($v) => $v['product_id']);
        try {
            $this->store_data(compact('data','data_items'));
        } catch (\Throwable $th) {
            //throw $th;
            return errorHandler('Error Creating Delivery', $th);
        }
        
        //return with successfull message
        return new RedirectResponse(route('biller.deliveries.index'), ['flash_success' => 'Delivery Frequency Created Successfully!!']);
    }

    public function store_data(array $input){
        DB::beginTransaction();
        $data = $input['data'];
        foreach ($data as $key => $val) {
            if(in_array($key,['date']))
                $data[$key] = date_for_database($val);
        }
        $result = Delivery::create($data);
        //line items
        $data_items = $input['data_items'];
        $data_items = array_map(function ($v) use($result) {
            return array_replace($v, [
                'delivery_id' => $result->id, 
                'ins' => $result->ins,
                'planned_qty' =>  floatval(str_replace(',', '', $v['planned_qty'])),
                'delivered_qty' => floatval(str_replace(',', '', $v['delivered_qty'])),
                'returned_qty' => floatval(str_replace(',', '', $v['returned_qty'])),
            ]);
        }, $data_items);
        DeliveryItem::insert($data_items);
        if ($result) {
            DB::commit();
            return $result;
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param EditdeliveryRequestNamespace $request
     * @return \App\Http\Responses\Focus\delivery\EditResponse
     */
    public function edit(Delivery $delivery)
    {
        $orders = Orders::whereIn('status',['confirmed','started'])->get();
        $users = Hrm::all();
        return view('focus.deliveries.edit', compact('delivery','orders','users'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdatedeliveryRequestNamespace $request
     * @param App\Models\delivery\delivery $delivery
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(Request $request, Delivery $delivery)
    {
        //Input received from the request
        $input = $request->except(['_token', 'ins']);
        //return with successfull message
        return new RedirectResponse(route('biller.deliveries.index'), ['flash_success' => 'Delivery Frequency Updated Successfully!!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeletedeliveryRequestNamespace $request
     * @param App\Models\delivery\delivery $delivery
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(Delivery $delivery)
    {
        //returning with successfull message
        return new RedirectResponse(route('biller.deliveries.index'), ['flash_success' => 'Delivery Frequency Deleted Successfully!!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeletedeliveryRequestNamespace $request
     * @param App\Models\delivery\delivery $delivery
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(Delivery $delivery)
    {
        return new ViewResponse('focus.deliveries.view', compact('delivery'));
    }

}
