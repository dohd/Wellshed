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
use App\Jobs\SendDeliveryStatusEmail;
use App\Jobs\SendEnRouteNotificationJob;
use App\Jobs\SendStatusEmailJob;
use App\Models\Company\Company;
use App\Models\Company\RecipientSetting;
use App\Models\customer\Customer;
use App\Models\delivery_schedule\DeliverySchedule;
use App\Models\delivery_schedule\DeliveryScheduleItem;
use App\Models\orders\Orders;
use App\Models\product\Product;
use App\Models\product\ProductVariation;
use App\Models\stock_transaction\StockTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Mpdf\Mpdf;

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
        $orders = Orders::get();
        $customers = Customer::all();
        return new ViewResponse('focus.delivery_schedules.index', compact('orders','customers'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param Createdelivery_scheduleRequestNamespace $request
     * @return \App\Http\Responses\Focus\delivery_schedule\CreateResponse
     */
    public function create()
    {
        $customers = Customer::all();
        return view('focus.delivery_schedules.create', compact('customers'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Storedelivery_scheduleRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(Request $request)
    {
        $input = $request->except(['_token', 'ins']);
        $input['ins'] = auth()->user()->ins;

        try {
            DB::beginTransaction();

            $order = Orders::find($input['order_id']);

            $schedule = DeliverySchedule::create([
                'tid' => (DeliverySchedule::max('tid') ?? 0) + 1,
                'customer_id' => $order->customer_id ?? '',
                'order_id' => $order->id,
                'delivery_date' => $input['delivery_date'],
                'delivery_time' => '',
                'delivery_frequency_id' => '',
                'status' => 'scheduled',
                'ins' => $order->ins,
                'user_id' => auth()->user()->id,
            ]);

            // Use items from the request instead of order->items
            $items = collect($input['items'])->map(function ($item) use ($schedule) {
                return [
                    'delivery_schedule_id' => $schedule->id,
                    'order_item_id' => $item['order_item_id'], // assuming this refers to the order_item_id
                    'product_id' => $item['product_id'], // or change this if you have product_id elsewhere
                    'qty' => $item['qty'],
                    'rate' => $item['rate'],
                    'amount' => $item['amount'],
                    'ins' => $schedule->ins,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->toArray();

            if (!empty($items)) {
                DeliveryScheduleItem::insert($items);
            }

            DB::commit();
        } catch (\Throwable $th) {
            return errorHandler('Error Creating Delivery Schedule', $th);
        }

        return new RedirectResponse(
            route('biller.delivery_schedules.index'),
            ['flash_success' => 'Delivery Frequency Created Successfully!!']
        );
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param Editdelivery_scheduleRequestNamespace $request
     * @return \App\Http\Responses\Focus\delivery_schedule\EditResponse
     */
    public function edit(DeliverySchedule $delivery_schedule)
    {
        $customers = Customer::all();
        return view('focus.delivery_schedules.edit', compact('delivery_schedule','customers'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Updatedelivery_scheduleRequestNamespace $request
     * @param App\Models\delivery_schedule\delivery_schedule $delivery_schedule
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(Request $request, DeliverySchedule $delivery_schedule)
    {
        // Validate (optional, but recommended)
        
        $request->validate([
            'delivery_date' => 'required|date',
            'items.*.id' => 'required|exists:delivery_schedule_items,id',
            'items.*.qty' => 'required|numeric|min:0',
            'items.*.rate' => 'required|numeric|min:0',
            // 'items.*.amount' => 'required|numeric|min:0',
        ]);
        // Format delivery date for database
        $delivery_schedule->update([
            'delivery_date' => date_for_database($request->delivery_date),
            'remarks' => $request->remarks,
        ]);

        // Loop through each item and update it
        foreach ($request->items as $itemData) {
            $item = $delivery_schedule->items()->find($itemData['id']);

            if ($item) {
                $item->update([
                    'qty' => numberClean($itemData['qty']),
                    'rate' => numberClean($itemData['rate']),
                    'amount' => numberClean($itemData['amount']),
                ]);
            }
        }

        return redirect()
            ->route('biller.delivery_schedules.index')
            ->with('flash_success', 'Delivery Schedule Updated Successfully!');
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
        $delivery_schedules = DeliverySchedule::where('order_id',$request->order_id)->where('status','en_route')->where('delivery_date',date('Y-m-d'))->get();
        $delivery_schedules->map(function($v){
            $v->name = gen4tid('DS-',$v->tid).'-'.dateFormat($v->delivery_date).'-'.@$v->delivery_frequency->delivery_days;
            return $v;
        });
        return response()->json($delivery_schedules);
    }
    public function get_schedule_items(Request $request)
    {
        $delivery_schedule = DeliverySchedule::where('id',$request->delivery_schedule_id)->first();
        $items = $delivery_schedule->items()->with('product')->get();
        $items->map(function($v){
            $v->rate = $v->order_item ? $v->order_item->rate : 0;
            $v->itemtax = $v->order_item ? $v->order_item->itemtax : 0;
            $v->delivery_schedule_item_id = $v->id;
            $v->cost_of_bottle = $v->product ? $v->product->cost_of_bottle : 0;
            return $v;
        });
        
        return response()->json($items);
    }

    public function update_status(Request $request)
    {
        DB::beginTransaction();
        $request->validate([
            'id' => 'required|integer',
            'status' => 'required|string',
            'status_note' => 'nullable|string',
        ]);

        $schedule = DeliverySchedule::with('items.product')->findOrFail($request->id);

        $previousStatus = $schedule->status;
        $newStatus = $request->status;

        // Update fields based on status
        if ($newStatus === 'en_route') {
            $schedule->dispatched_by = auth()->id();
        }

        $schedule->status = $newStatus;
        $schedule->status_note = $request->status_note;
        $schedule->save();

        // Get recipient settings only when required
        $recipt_setting = RecipientSetting::where('ins', auth()->user()->ins)
            ->where('type', 'dispatch_notification')
            ->first();

        /**
         * ✅ Handle stock reduction only if:
         * - New status is en_route
         * - Previous status was NOT already en_route (avoid double deduction)
         */
        if ($newStatus === 'en_route' && $previousStatus !== 'en_route') {

            foreach ($schedule->items as $item) {
                if ($item->product) {

                    // $item->product->decrement('qty', $item->qty);

                    StockTransaction::create([
                        'stock_item_id' => $item->product_id,
                        'date' => now()->format('Y-m-d'),
                        'qty' => -$item->qty,
                        'price' => $item->product->price ?? 0,
                        'type' => 'sale',
                        'tid' => $schedule->tid,
                    ]);
                }
            }

            // Notifications only sent once
            if ($recipt_setting) {
                if ($recipt_setting->email === 'yes') {
                    SendDeliveryStatusEmail::dispatch($schedule, $schedule->ins);
                }

                if ($recipt_setting->sms === 'yes') {
                    dispatch(new SendEnRouteNotificationJob($schedule->id));
                }
            }
        }elseif(($newStatus === 'cancelled' || $newStatus === 'failed') && $previousStatus === 'en_route')
        {
            StockTransaction::where('tid', $schedule->tid)
            ->where('type', 'sale')
            ->delete();
        }
        DB::commit();
        return response()->json(['success' => true]);
    }


    public function daily_delivery_report()
    {
        return view('focus.delivery_schedules.daily_delivery_report');
    }

    public function exportPdf(Request $request)
    {
        $start_date = $request->start_date ?? Carbon::today()->toDateString();
        $end_date = $request->end_date ?? Carbon::today()->toDateString();

        $report = DeliverySchedule::with(['order.customer', 'delivery_frequency', 'items.product'])
            ->whereBetween('delivery_date', [date_for_database($start_date), date_for_database($end_date)])
            ->get();
        $company = Company::find(auth()->user()->ins);

        $html = view('focus.delivery_schedules.daily_delivery_pdf', compact('report', 'start_date','end_date', 'company'))->render();

        // PDF settings
        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
        ]);

        $mpdf->SetTitle('Daily Delivery Report - ' . date('d-m-Y'));
        $mpdf->WriteHTML($html);

        return response($mpdf->Output('', 'i'), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="daily-delivery-report-' . date('d-m-Y') . '.pdf"');
    }

    public function product_movement_report()
    {
        $products = ProductVariation::all();
        return view('focus.delivery_schedules.product_movement_report',compact('products'));
    }

    public function product_movement_pdf(Request $request)
    {
        $start_date = $request->start_date ?? Carbon::today()->toDateString();
        $end_date = $request->end_date ?? Carbon::today()->toDateString();
        $product_id = $request->product_id;
        $schedule_items = DeliveryScheduleItem::where('product_id',$product_id)
                ->whereHas('schedule', function($q) use($start_date, $end_date){
                    $q->whereIn('status',['en_route','delivered'])->whereBetween('delivery_date',[date_for_database($start_date), date_for_database($end_date)]);
                })->get();
        $company = Company::find(auth()->user()->ins);

        $html = view('focus.delivery_schedules.product_movement_pdf', compact('schedule_items', 'start_date','end_date', 'company'))->render();

        // PDF settings
        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
        ]);

        $mpdf->SetTitle('Daily Delivery Report - ' . date('d-m-Y'));
        $mpdf->WriteHTML($html);

        return response($mpdf->Output('', 'i'), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="daily-delivery-report-' . date('d-m-Y') . '.pdf"');
    }

}
