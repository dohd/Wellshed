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
use App\Models\delivery\Delivery;
use App\Models\delivery_schedule\DeliverySchedule;
use App\Models\delivery_schedule\DeliveryScheduleItem;
use App\Models\orders\Orders;
use App\Models\payment_receipt\PaymentReceipt;
use App\Models\product\Product;
use App\Models\product\ProductVariation;
use App\Models\stock_transaction\StockTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Mpdf\Mpdf;
use View;

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
        return new ViewResponse('focus.delivery_schedules.index', compact('orders', 'customers'));
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
        return view('focus.delivery_schedules.edit', compact('delivery_schedule', 'customers'));
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
        $delivery_schedules = DeliverySchedule::where('order_id', $request->order_id)->where('status', 'en_route')->where('delivery_date', date('Y-m-d'))->get();
        $delivery_schedules->map(function ($v) {
            $v->name = gen4tid('DS-', $v->tid) . '-' . dateFormat($v->delivery_date);
            return $v;
        });
        return response()->json($delivery_schedules);
    }
    public function get_schedule_items(Request $request)
    {
        $delivery_schedule = DeliverySchedule::where('id', $request->delivery_schedule_id)->first();
        $items = $delivery_schedule->items()->with('product')->get();
        $items->map(function ($v) {
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

            $tid = StockTransaction::max('tid') + 1;
            foreach ($schedule->items as $item) {
                if ($item->product) {

                    // $item->product->decrement('qty', $item->qty);

                    StockTransaction::create([
                        'stock_item_id' => $item->product_id,
                        'date' => now()->format('Y-m-d'),
                        'qty' => -$item->qty,
                        'price' => $item->product->price ?? 0,
                        'type' => 'sale',
                        'tid' => $tid,
                        'dispatch_id' => $schedule->id,
                        'dispatch_item_id' => $item->id,
                        'product_category_id' => $item->product ? $item->product->productcategory_id : '',
                        'created_by' => auth()->user()->id,
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
        } elseif (($newStatus === 'cancelled' || $newStatus === 'failed') && $previousStatus === 'en_route') {
            foreach ($schedule->items as $item) {

                StockTransaction::where(['dispatch_item_id'=> $item->id,'dispatch_id' => $schedule->id,])
                    ->where('type', 'sale')
                    ->update(['deleted_at' => now(), 'deleted_by' => auth()->id()]);
            }
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
        // Define date range
        $start_date = $request->start_date ?? Carbon::today()->toDateString();
        $end_date = $request->end_date ?? Carbon::today()->toDateString();

        $start = date_for_database($start_date);
        $end = date_for_database($end_date);

        // ============================
        // 1️⃣ FETCH ACTIVE ORDERS (based on range)
        // ============================
        $orders = Orders::with(['items.product.product.category', 'customer'])
            ->where('status', 'completed')
            ->where(function ($q) use ($start, $end) {
                $q->whereDate('start_month', '<=', $end)
                    ->whereDate('end_month', '>=', $start);
            })
            ->get();

        // Calculate basic metrics
        $grossOrders = $orders->sum(fn($o) => $o->items->sum('amount'));
        $taxes = $orders->sum('tax');
        $ordersCount = $orders->count();

        // ============================
        // 2️⃣ CATEGORY BREAKDOWN
        // ============================
        $categories = [];
        $totalUnits = $totalGross = $totalTax = $totalNet = 0;

        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                $catName = $item->product->product->category->title ?? 'Uncategorized';
                if (!isset($categories[$catName])) {
                    $categories[$catName] = [
                        'name' => $catName,
                        'units' => 0,
                        'gross' => 0,
                        'tax' => 0,
                        'net' => 0,
                    ];
                }

                $categories[$catName]['units'] += $item->qty;
                $categories[$catName]['gross'] += $item->amount;
                $categories[$catName]['tax'] += $item->tax ?? 0;
                $categories[$catName]['net'] += $item->amount + ($item->tax ?? 0);

                $totalUnits += $item->qty;
                $totalGross += $item->amount;
                $totalTax += $item->tax ?? 0;
                $totalNet += $item->amount + ($item->tax ?? 0);
            }
        }

        foreach ($categories as &$cat) {
            $cat['percentage'] = $totalNet > 0
                ? ($cat['net'] / $totalNet) * 100
                : 0;
        }

        $totals = [
            'units' => $totalUnits,
            'gross' => $totalGross,
            'tax' => $totalTax,
            'net' => $totalNet,
        ];

        // ============================
        // 3️⃣ PAYMENT RECEIPTS
        // ============================
        $payments = PaymentReceipt::selectRaw('payment_method, SUM(amount) as total_amount')
            ->where('entry_type', 'receive')
            ->whereBetween('date', [$start, $end])
            ->groupBy('payment_method')
            ->get()
            ->map(fn($p) => [
                'mode' => ucfirst($p->payment_method),
                'amount' => $p->total_amount,
            ])->toArray();

        // ============================
        // 4️⃣ DELIVERY SCHEDULE SUMMARY (planned)
        // ============================
        $deliverySchedules = DeliverySchedule::with('items')
            ->whereBetween('delivery_date', [$start, $end])
            ->get()
            ->map(function ($schedule) {
                return (object)[
                    'delivery_date' => Carbon::parse($schedule->delivery_date),
                    'orders_count' => 1,
                    'items_count' => $schedule->items->sum('qty'),
                    'total_amount' => $schedule->items->sum('amount'),
                ];
            });

        // ============================
        // 5️⃣ ACTUAL DELIVERIES SUMMARY (executed)
        // ============================
        $deliveries = Delivery::with('items')
            ->whereBetween('date', [$start, $end])
            ->get()
            ->map(function ($delivery) {
                return (object)[
                    'delivered_on' => Carbon::parse($delivery->date),
                    'orders_count' => 1,
                    'items_count' => $delivery->items->sum('delivered_qty'),
                    'total_amount' => $delivery->items->sum('amount'),
                ];
            });

        // ============================
        // 6️⃣ CLOSING BALANCES (cash)
        // ============================
        $openingCash = PaymentReceipt::where('entry_type', 'receive')
            ->whereIn('payment_method', ['cash','mpesa'])
            ->whereDate('date', '<', $start)
            ->sum('amount');

        $cashReceived = PaymentReceipt::where('entry_type', 'receive')
            ->whereIn('payment_method', ['cash','mpesa'])
            ->whereBetween('date', [$start, $end])
            ->sum('amount');

        $closingBalance = $openingCash + $cashReceived;

        $closingBalances = [
            'opening' => $openingCash,
            'received' => $cashReceived,
            'closing' => $closingBalance,
        ];

        // ============================
        // 7️⃣ COMPANY INFO
        // ============================
        $company = Company::find(auth()->user()->ins);

        // ============================
        // 8️⃣ SUMMARY DATA FOR VIEW
        // ============================
        $summary = [
            'gross_orders' => $grossOrders,
            'taxes' => $taxes,
            'total_receipts' => collect($payments)->sum('amount'),
            'orders_count' => $ordersCount,
        ];

        // ============================
        // 9️⃣ RENDER PDF VIEW
        // ============================
        $html = View::make('focus.delivery_schedules.daily_delivery_pdf', [
            'start_date' => Carbon::parse($start_date),
            'end_date' => Carbon::parse($end_date),
            'company' => $company,
            'summary' => $summary,
            'categories' => $categories,
            'totals' => $totals,
            'payments' => $payments,
            'deliverySchedules' => $deliverySchedules,
            'deliveries' => $deliveries,
            'closingBalances' => $closingBalances,
        ])->render();

        // ============================
        // 🔟 GENERATE PDF
        // ============================
        $mpdf = new Mpdf(['mode' => 'utf-8', 'format' => 'A4']);
        $mpdf->SetTitle('Daily Orders Summary - ' . Carbon::now()->format('d M Y'));
        $mpdf->SetAuthor(auth()->user()->name ?? 'System');
        $mpdf->SetHTMLFooter('
        <table width="100%" style="font-size: 9px; border-top: 0.1mm solid #000;">
            <tr>
                <td width="50%" align="left">Page {PAGENO} of {nbpg}</td>
                <td width="50%" align="right">Generated on ' . now()->format('d M Y, h:i A') . '</td>
            </tr>
        </table>
    ');
        $mpdf->WriteHTML($html);

        return response($mpdf->Output('daily-orders-summary-' . date('d-m-Y') . '.pdf', 'I'))
            ->header('Content-Type', 'application/pdf');
    }


    public function product_movement_report()
    {
        $products = ProductVariation::all();
        return view('focus.delivery_schedules.product_movement_report', compact('products'));
    }

    public function product_movement_pdf(Request $request)
    {
        $start_date = $request->start_date ?? Carbon::today()->toDateString();
        $end_date = $request->end_date ?? Carbon::today()->toDateString();
        $product_id = $request->product_id; // optional

        // ✅ Build query directly using product_id
        $query = StockTransaction::with(['product'])
            ->whereIn('type', ['adjustment', 'transfer', 'sale'])
            ->whereBetween('date', [
                date_for_database($start_date),
                date_for_database($end_date)
            ]);

        if (!empty($product_id)) {
            $query->where('stock_item_id', $product_id);
        }

        $transactions = $query->orderBy('date', 'asc')->get();

        // ✅ Movement Section
        $movements = $transactions->map(function ($t) {
            $product = $t->product ?? null;

            if ($t->type === 'sale') {
                $source = 'Dispatch';
            } elseif ($t->type === 'transfer') {
                $source = 'Transfer';
            } elseif ($t->type === 'adjustment') {
                $source = 'Adjustment';
            } else {
                $source = ucfirst($t->type);
            }

            return [
                'date' => Carbon::parse($t->date)->format('d-m-Y'),
                'sku' => $product->code ?? '-',
                'item_name' => $product->name ?? '-',
                'qty' => $t->qty,
                'rate' => $t->price,
                'source' => $source,
                'reference_no' => gen4tid(strtoupper(substr($t->type, 0, 3)) . '-', $t->tid),
            ];
        });


        // ✅ Summary Calculation (group by product)
        $summary = collect();

        $productGroups = $transactions->groupBy('stock_item_id');

        foreach ($productGroups as $productId => $group) {
            $product = $group->first()->product ?? null;

            // --- Opening Quantity (transactions before start date)
            $opening_qty = StockTransaction::where('stock_item_id', $productId)
                ->where('date', '<', date_for_database($start_date))
                ->sum('qty');

            // --- Movement within range
            $inbound = $group->where('type', 'transfer')->sum('qty');
            $outbound = $group->where('type', 'sale')->sum('qty');
            $adjustments = $group->where('type', 'adjustment')->sum('qty');

            // --- Closing Qty & Value
            $closing_qty = $opening_qty + $inbound - $outbound + $adjustments;
            $purchase_price = $product->purchase_price ?? $group->avg('price') ?? 0;
            $closing_value = $purchase_price * $closing_qty;

            $summary->push([
                'sku' => $product->code ?? '-',
                'item_name' => $product->name ?? '-',
                'opening_qty' => $opening_qty,
                'inbound' => $inbound,
                'outbound' => $outbound,
                'adjustments' => $adjustments,
                'closing_qty' => $closing_qty,
                'closing_value' => $closing_value,
            ]);
        }

        // ✅ Company Info
        $company = Company::find(auth()->user()->ins);

        // ✅ Render Blade Template
        $html = View::make('focus.delivery_schedules.product_movement_pdf', [
            'company' => $company,
            'movements' => $movements,
            'summary' => $summary,
            'start_date' => $start_date,
            'end_date' => $end_date,
        ])->render();

        // ✅ Configure mPDF
        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
        ]);

        $mpdf->SetTitle('Product Movement Report - ' . Carbon::now()->format('d-m-Y'));
        $mpdf->SetAuthor(auth()->user()->first_name ?? 'System');
        $mpdf->SetHTMLFooter('
            <table width="100%" style="font-size: 9px; border-top: 0.1mm solid #000;">
                <tr>
                    <td width="50%" align="left">Page {PAGENO} of {nbpg}</td>
                    <td width="50%" align="right">Generated on ' . now()->format('d M Y, h:i A') . '</td>
                </tr>
            </table>
        ');

        $mpdf->WriteHTML($html);

        return response($mpdf->Output('product-movement-report-' . date('d-m-Y') . '.pdf', 'I'))
            ->header('Content-Type', 'application/pdf');
    }
}
