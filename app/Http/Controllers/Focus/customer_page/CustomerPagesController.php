<?php

namespace App\Http\Controllers\Focus\customer_page;

use App\Http\Controllers\Controller;
use App\Models\customer\Customer;
use App\Models\delivery_frequency\DeliveryFreq;
use App\Models\delivery_schedule\DeliverySchedule;
use App\Models\delivery_schedule\DeliveryScheduleItem;
use App\Models\orders\Orders;
use App\Models\orders\OrdersItem;
use App\Models\payment_receipt\PaymentReceipt;
use App\Models\product\ProductVariation;
use App\Models\subscription\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;

class CustomerPagesController extends Controller
{
    /**
     * Landing Home Page
     * */
    public function home()
    {
        // dd(auth()->user()->toArray());
        $customer = Customer::where('id', auth()->user()->customer_id)->first();
        $order = Orders::where('customer_id', $customer->id)->whereIn('status', ['confirmed', 'started'])->first();
        $prev_schedules = DeliverySchedule::where('customer_id', $customer->id)->where('status', 'delivered')->take(5)->get();
        $incoming_schedules = DeliverySchedule::where('customer_id', $customer->id)->whereIn('status', ['scheduled', 'en_route'])->take(2)->get();
        // dd($incoming_schedules);
        return view('focus.pages.home', compact('customer', 'prev_schedules', 'incoming_schedules'));
    }

    /**
     * Order Page
     * */
    public function orders()
    {
        $recurring = 0;
        $customer = Customer::where('id', auth()->user()->customer_id)->first();
        $products = collect();
        $order = Orders::where('customer_id', $customer->id)->whereNotIn('status', ['cancelled', 'completed'])->first();
        if (!$order) {
            $recurring = 1;
            $subscription = $customer->subscriptions()->where('status', 'active')->first();
            $package = $subscription->package;
            //Subscription products only
            $products = ProductVariation::where('type', 'full')
                ->where('id', $package->productvar_id)
                ->get([
                    'id',
                    'name',
                    DB::raw('price * 1.16 as price'), // adds 16% VAT
                    'name as eta',
                ]);
            $products = $products->map(function ($q) use ($package) {
                $q->qty = $package->max_bottle;
                return $q;
            });
            // dd($products);

        } else {
            $recurring = 0;
            $products = ProductVariation::where('type', 'full')
                ->get([
                    'id',
                    'name',
                    DB::raw('price * 1.16 as price'), // adds 16% VAT
                    'name as eta',
                ]);
        }
        // dd($products);

        return view('focus.pages.orders', compact('products', 'recurring'));
    }

    public function track()
    {
        return view('focus.pages.track');
    }

    public function profile()
    {
        $customer = Customer::where('id', auth()->user()->customer_id)->first();
        return view('focus.pages.profile', compact('customer'));
    }

    public function delivery()
    {
        $recurring = 1;
        $qty = 0;
        $customer = Customer::where('id', auth()->user()->customer_id)->first();
        $order = Orders::where('customer_id', $customer->id)->whereNotIn('status', ['cancelled', 'completed'])->first();
        if (!$order) {
            $recurring = 0;
            $subscription = $customer->subscriptions()->where('status', 'active')->first();
            $package = $subscription->package;
            $qty = $package->max_bottle;
        }
        $customer = Customer::where('id', auth()->user()->customer_id)->first();
        $customer_zones = $customer->customer_zones()->with('location')->get();
        return view('focus.pages.delivery-details', compact('customer', 'customer_zones', 'recurring', 'qty'));
    }

    public function review()
    {
        $customer = Customer::where('id', auth()->user()->customer_id)->first();
        return view('focus.pages.review-order', compact('customer'));
    }

    public function thank_you()
    {
        return view('focus.pages.thank-you');
    }

    /**
     * Payments Page
     * */
    public function payments()
    {
        $balance = PaymentReceipt::selectRaw('SUM(debit-credit) total')->value('total');
        $receipts = PaymentReceipt::latest()->get();

        $customer = auth()->user()->customer;
        $subscription = $customer->subscription;
        $subscrPlan = optional($customer->subscription->package);
        if ($subscription->status !== 'active') $subscrPlan = null;

        $charges = PaymentReceipt::where('entry_type', 'debit')
        ->get(['id', 'tid', 'notes', 'amount'])
        ->map(function($v) {
            $v->tid = gen4tid('RCPT-', $v->tid);
            return $v;
        });

        return view('focus.pages.payments', 
            compact('balance', 'receipts', 'customer', 'subscrPlan', 'charges', 'subscription'),            
        );
    }

    public function subscriptions()
    {
        $customerId = auth()->user()->customer_id;
        $authsubscr = Subscription::with('package')->where('customer_id', $customerId)->latest()->first();
        $subscriptions = Subscription::with('package')->where('id', '!=', @$authsubscr->id)->get();
        $nextSchedule = DeliverySchedule::where('customer_id', $customerId)->where('status', 'scheduled')
            ->first();

        return view('focus.pages.subscriptions', compact('authsubscr', 'subscriptions', 'nextSchedule'));
    }
    public function my_orders()
    {
        $customer = Customer::where('id', auth()->user()->customer_id)->first();
        $orders = Orders::where('customer_id', $customer->id)->latest()->get();
        return view('focus.pages.my_orders', compact('orders'));
    }

    public function submit_order(Request $request)
    {
        $payload = json_decode($request->order_payload, true);
        $payment_id = $request->payment_id;

        if (!$payload) {
            return back()->withErrors('Invalid order payload')->withInput();
        }

        // ✅ Validate structure
        $validator = Validator::make($payload, [
            'customer.name'              => 'required|string|max:255',
            'customer.customer_id'       => 'required|numeric',
            'customer.order_type'        => 'required|string|in:one_time,recurring',
            'customer.frequency'         => 'nullable|string|in:daily,weekly,custom',
            'customer.delivery_days'     => 'nullable|array',
            'customer.week_numbers'      => 'nullable|array',
            'customer.qty_per_day'       => 'nullable|array',     // ✅ added
            'customer.delivery_date'     => 'nullable|date',
            'customer.start_month'       => 'nullable|date',
            'customer.locations_for_days' => 'nullable|array',
            'cart'                       => 'required|array|min:1',
            'total'                      => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();

        try {
            $customer = $payload['customer'];

            // ✅ Calculate order totals
            $total = numberClean($payload['total']);
            $subtotal = $total / 1.16;
            $tax = $total - $subtotal;

            // ✅ Create main order
            $order = Orders::create([
                'tid'          => (Orders::max('tid') ?? 0) + 1,
                'customer_id'  => $customer['customer_id'],
                'order_type'   => $customer['order_type'],
                'description'  => $customer['description'] ?? null,
                'frequency'    => $customer['order_type'] === 'recurring' ? ($customer['frequency'] ?? null) : null,
                'start_month'  => !empty($customer['start_month']) ? date_for_database($customer['start_month']) : now(),
                'subtotal'     => $subtotal,
                'taxable'      => $subtotal,
                'tax'          => $tax,
                'total'        => $total,
                'status'       => 'confirmed',
            ]);

            // ✅ Save order items
            foreach ($payload['cart'] as $item) {
                $product = ProductVariation::find($item['id']);
                $itemtax = $item['price'] - $product['price'];

                OrdersItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $item['id'],
                    'qty'        => $item['qty'],
                    'rate'       => $product['price'],
                    'tax_rate'   => 16,
                    'itemtax'    => $itemtax * $item['qty'],
                    'amount'     => $item['qty'] * $item['price'],
                ]);
            }

            // ✅ Handle recurring/custom frequency setup
            if ($order->order_type === 'recurring') {

                $deliveryDays   = $customer['delivery_days'] ?? [];
                $weekNumbers    = $customer['week_numbers'] ?? [];
                $locationsMap   = $customer['locations_for_days'] ?? [];
                $qtyPerDay      = $customer['qty_per_day'] ?? [];   // ✅ NEW

                DeliveryFreq::create([
                    'order_id'           => $order->id,
                    'frequency'          => $customer['frequency'] ?? null,
                    'delivery_days'      => json_encode($deliveryDays),
                    'week_numbers'       => json_encode($weekNumbers),
                    'qty_per_day'        => json_encode($qtyPerDay),     // ✅ NEW
                    'locations_for_days' => json_encode($locationsMap),
                    'expected_time'      => $customer['expected_time'] ?? null,
                    'user_id'            => auth()->id(),
                    'ins'                => $order->ins,
                ]);
            }

            // ✅ Create one-time delivery schedule
            if ($order->order_type === 'one_time') {

                $payment = PaymentReceipt::find($payment_id);
                if($payment){
                    $payment->order_id = $order->id;
                    $payment->update();
                    $order->update(['payment_status' => 'paid']);
                }
                $schedule = DeliverySchedule::create([
                    'tid'           => (DeliverySchedule::max('tid') ?? 0) + 1,
                    'order_id'      => $order->id,
                    'customer_id'   => $order->customer_id,
                    'delivery_date' => $customer['delivery_date'] ?? now(),
                    'status'        => 'scheduled',
                    'ins'           => $order->ins,
                    'user_id'       => $order->user_id,
                ]);

                foreach ($order->items as $item) {
                    DeliveryScheduleItem::create([
                        'delivery_schedule_id' => $schedule->id,
                        'order_item_id'        => $item->id,
                        'product_id'           => $item->product_id,
                        'qty'                  => $item->qty,
                        'rate'                 => $item->rate,
                        'amount'               => $item->amount,
                        'ins'                  => $order->ins,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('biller.customer_pages.thank_you')
                ->with('success', 'Order submitted successfully!');
        } catch (\Throwable $th) {
            DB::rollBack();
            \Log::error('Submit Order Error: ' . $th->getMessage());
            return back()->withErrors(['error' => 'Error creating order. Please try again.']);
        }
    }





    public function create_schedule($order, $customer)
    {
        try {
            DB::beginTransaction();
            $order = Orders::find($order->id);
            $schedule = DeliverySchedule::create([
                'tid' => (DeliverySchedule::max('tid') ?? 0) + 1,
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'delivery_date' => $customer['delivery_date'],
                'delivery_time' => '',
                'delivery_frequency_id' => '',
                'status' => 'scheduled',
                'ins' => $order->ins,
                'user_id' => auth()->user()->id,
            ]);

            $items = $order->items->map(function ($item) use ($schedule) {
                return [
                    'delivery_schedule_id' => $schedule->id,
                    'order_item_id' => $item->id,
                    'product_id' => $item->product_id,
                    'qty' => $item->qty,
                    'rate' => $item->rate,
                    'amount' => $item->amount,
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
    }
}
