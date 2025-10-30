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
use App\Jobs\SendDeliveryEmailJob;
use App\Jobs\SendDeliveryNotificationJob;
use App\Models\Company\RecipientSetting;
use App\Models\customer\Customer;
use App\Models\delivery\Delivery;
use App\Models\delivery\DeliveryItem;
use App\Models\delivery_schedule\DeliverySchedule;
use App\Models\hrm\Hrm;
use App\Models\orders\Orders;
use App\Models\send_sms\SendSms;
use App\Repositories\AdvantaSmsService;
use App\Repositories\Focus\general\RosesmsRepository;
use Exception;
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
        $customers = Customer::all();
        $orders = Orders::whereIn('status',['confirmed','started'])->get();
        $users = Hrm::all();
        return view('focus.deliveries.create', compact('orders','users','customers'));
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
        $data = $request->only(['customer_id','order_id','delivery_schedule_id','date','driver_id','description']);
        $data_items = $request->only('product_id','planned_qty','delivered_qty','returned_qty','remaining_qty','order_item_id','delivery_schedule_item_id');
        $data_items = modify_array($data_items);
        $data_items = array_filter($data_items, fn($v) => $v['product_id']);
        try {
            $this->store_data(compact('data','data_items'));
        } catch (\Throwable $th) {dd($th);
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
            $delivery_schedule = $result->delivery_schedule;
            foreach ($result->items as $item) {
                $delivery_item = $item;
                $delivery_schedule_item = $item->delivery_schedule_item;
                if(!$delivery_schedule_item) continue;
                $delivery_schedule_item->delivered_qty = $delivery_item->delivered_qty;
                $delivery_schedule_item->returned_qty = $delivery_item->returned_qty;
                $delivery_schedule_item->remaining_qty = $delivery_item->remaining_qty;
                $delivery_schedule_item->update();
            }
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
        $customers = Customer::all();
        return view('focus.deliveries.edit', compact('delivery','orders','users','customers'));
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
        $data = $request->only(['customer_id','order_id','delivery_schedule_id','date','driver_id','description']);
        $data_items = $request->only('product_id','planned_qty','delivered_qty','remaining_qty','returned_qty','id');
        $data_items = modify_array($data_items);
        $data_items = array_filter($data_items, fn($v) => $v['product_id']);
        try {
            $this->update_data($delivery, compact('data','data_items'));
        } catch (\Throwable $th) {dd($th);
            //throw $th;
            return errorHandler('Error Updating Delivery', $th);
        }
        //return with successfull message
        return new RedirectResponse(route('biller.deliveries.index'), ['flash_success' => 'Delivery Frequency Updated Successfully!!']);
    }

    public function update_data($delivery, array $input)
    {
        DB::beginTransaction();
        $data = $input['data'];
        foreach ($data as $key => $val) {
            if(in_array($key,['date']))
                $data[$key] = date_for_database($val);
        }
        $delivery->update($data);
        $data_items = $input['data_items'];
        $item_ids = array_map(function ($v) { return $v['id']; }, $data_items);
        $delivery->items()->whereNotIn('id', $item_ids)->delete();

        // create or update items
        foreach($data_items as $item) {
            foreach ($item as $key => $val) {
                if (in_array($key, ['product_price', 'product_subtotal', 'buy_price', 'estimate_qty']))
                    $item[$key] = floatval(str_replace(',', '', $val));
            }
            $delivery_item = DeliveryItem::firstOrNew(['id' => $item['id']]);
            $delivery_item->fill(array_replace($item, ['delivery_id' => $delivery['id'], 'ins' => $delivery['ins']]));
            if (!$delivery_item->id) unset($delivery_item->id);
            $delivery_item->save();
        }

        if($delivery){
            DB::commit();
            foreach ($delivery->items as $item) {
                $delivery_item = $item;
                $delivery_schedule_item = $item->delivery_schedule_item;
                if(!$delivery_schedule_item) continue;
                $delivery_schedule_item->delivered_qty = $delivery_item->delivered_qty;
                $delivery_schedule_item->returned_qty = $delivery_item->returned_qty;
                $delivery_schedule_item->remaining_qty = $delivery_item->remaining_qty;
                $delivery_schedule_item->update();
            }
            return true;
        }
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
        try {
            $delivery->delivery_schedule->status = 'en_route';
            $delivery->delivery_schedule->update();
            $delivery->items()->delete();
            $delivery->delete();
        } catch (\Throwable $th) {
            //throw $th;
            return errorHandler('Error Deleting Deliveries',$th);
        }
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

    public function change_status(Request $request)
    {
        $request->validate([
            'delivery_id' => 'required|exists:deliveries,id',
            'status' => 'required|string',
            'status_note' => 'nullable|string',
        ]);

        $delivery = Delivery::find($request->delivery_id);
        $delivery->update(['status' => $request->status,'status_note' => $request->status_note]);
        $recipt_setting = RecipientSetting::where('ins',auth()->user()->ins)->where('type','dispatch_notification')->first();
        if($delivery->status == 'delivered')
        {
            $delivery_schedule = $delivery->delivery_schedule;
            $delivery_schedule->status = 'delivered';
            $delivery_schedule->update();
            if($recipt_setting->email == 'yes'){
                SendDeliveryEmailJob::dispatch($delivery->id, auth()->user()->ins);
            }
            if($recipt_setting->sms == 'yes'){
                SendDeliveryNotificationJob::dispatch($delivery->id);
            }
        }
        //schedules count
        $all_schedules = DeliverySchedule::whereIn('status',['delivered','scheduled','en_route'])->where('order_id',$delivery->order_id)->count();
        //deliveries
        $deliveries = Delivery::where('status','delivered')->where('order_id',$delivery->order_id)->count();
        if($all_schedules == $deliveries){
            $order = $delivery->order;
            if($order){
                $order->status = 'completed';
                $order->update();
            }
        }elseif ($deliveries > 0 && $deliveries < $all_schedules) {
            $order = $delivery->order;
            if($order){
                $order->status = 'started';
                $order->update();
            }
        }


        return response()->json([
            'status' => 'success',
            'message' => 'Delivery status updated successfully.',
        ]);
    }


}
