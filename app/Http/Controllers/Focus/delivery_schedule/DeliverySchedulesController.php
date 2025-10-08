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
namespace App\Http\Controllers\Focus\delivery_schedule;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\delivery_schedule\DeliverySchedule;
use App\Models\delivery_schedule\DeliveryScheduleItem;

/**
 * DeliverySchedulesController
 */
class DeliverySchedulesController extends Controller
{
    

    /**
     * Display a listing of the resource.
     *
     * @return \App\Http\Responses\ViewResponse
     */
    public function index()
    {
        return new ViewResponse('focus.delivery_schedules.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param Createdelivery_scheduleRequestNamespace $request
     * @return \App\Http\Responses\Focus\delivery_schedule\CreateResponse
     */
    public function create()
    {
        return view('focus.delivery_schedules.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Storedelivery_scheduleRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(Request $request)
    {
        //Input received from the request
        $input = $request->except(['_token', 'ins']);
        $input['ins'] = auth()->user()->ins;
        //return with successfull message
        return new RedirectResponse(route('biller.delivery_schedules.index'), ['flash_success' => 'Delivery Frequency Created Successfully!!']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Editdelivery_scheduleRequestNamespace $request
     * @return \App\Http\Responses\Focus\delivery_schedule\EditResponse
     */
    public function edit(DeliverySchedule $delivery_schedule)
    {
        return view('focus.delivery_schedules.edit', compact('delivery_schedule'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Updatedelivery_scheduleRequestNamespace $request
     * @param App\Models\delivery_schedule\delivery_schedule $delivery_schedule
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(Request $request, DeliverySchedule $delivery)
    {
        //Input received from the request
        $input = $request->except(['_token', 'ins']);
        //return with successfull message
        return new RedirectResponse(route('biller.delivery_schedules.index'), ['flash_success' => 'Delivery Frequency Updated Successfully!!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Deletedelivery_scheduleRequestNamespace $request
     * @param App\Models\delivery_schedule\delivery_schedule $delivery_schedule
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(DeliverySchedule $delivery_schedule)
    {
        //returning with successfull message
        return new RedirectResponse(route('biller.delivery_schedules.index'), ['flash_success' => 'Delivery Frequency Deleted Successfully!!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Deletedelivery_scheduleRequestNamespace $request
     * @param App\Models\delivery_schedule\delivery_schedule $delivery_schedule
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(DeliverySchedule $delivery_schedule)
    {
        return new ViewResponse('focus.delivery_schedules.view', compact('delivery_schedule'));
    }

    public function get_schedules(Request $request)
    {
        $delivery_schedules = DeliverySchedule::where('order_id',$request->order_id)->get();
        $delivery_schedules->map(function($v){
            $v->name = dateFormat($v->delivery_date).'-'.@$v->delivery_frequency->delivery_days;
            return $v;
        });
        return response()->json($delivery_schedules);
    }
    public function get_schedule_items(Request $request)
    {
        $delivery_schedule = DeliverySchedule::where('id',$request->delivery_schedule_id)->first();
        $items = $delivery_schedule->items()->with('product')->get();
        return response()->json($items);
    }

}
