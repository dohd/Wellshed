<?php

namespace App\Http\Controllers\Focus\customer_page;

use App\Http\Controllers\Controller;
use App\Models\customer\Customer;
use App\Models\delivery_schedule\DeliverySchedule;
use App\Models\delivery_schedule\DeliveryScheduleItem;
use App\Models\orders\Orders;
use App\Models\orders\OrdersItem;
use App\Models\product\ProductVariation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;

class CustomerPagesController extends Controller
{
    public function home()
    {
        $customer = Customer::where('id', auth()->user()->customer_id)->first();
        $order = Orders::where('customer_id', $customer->id)->whereIn('status', ['confirmed', 'started'])->first();
        $prev_schedules = DeliverySchedule::where('customer_id', $customer->id)->where('status', 'delivered')->take(5)->get();
        $incoming_schedules = DeliverySchedule::where('customer_id', $customer->id)->whereIn('status', ['scheduled', 'en_route'])->take(2)->get();
        // dd($incoming_schedules);
        return view('focus.pages.home', compact('customer', 'prev_schedules', 'incoming_schedules'));
    }
    public function orders()
    {
        $products = ProductVariation::where('type', 'full')->get(['id', 'name', 'price', 'name as eta']);
        return view('focus.pages.orders', compact('products'));
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
        $customer = Customer::where('id', auth()->user()->customer_id)->first();
        return view('focus.pages.delivery-details', compact('customer'));
    }
    public function review()
    {
        return view('focus.pages.review-order');
    }
    public function thank_you()
    {
        return view('focus.pages.thank-you');
    }

    public function payments()
    {
        return view('focus.pages.payments');
    }
    public function subscriptions()
    {
        return view('focus.pages.subscriptions');
    }
    public function my_orders()
    {
        $customer = Customer::where('id', auth()->user()->customer_id)->first();
        $orders = Orders::where('customer_id', $customer->id)->get();
        return view('focus.pages.my_orders', compact('orders'));
    }

    public function submit_order(Request $request)
    {
        // Decode payload into array
        $payload = json_decode($request->order_payload, true);

        if (!$payload) {
            return back()->withErrors('Invalid order payload')->withInput();
        }

        // ✅ Validate payload normally (errors redirect back)
        $validator = Validator::make($payload, [
            'customer.name'          => 'required|string|max:255',
            'customer.customer_id'   => 'required|numeric',
            'customer.order_type'    => 'required|string',
            'customer.frequency'     => 'nullable|string',
            'customer.delivery_date' => 'required|date',
            'customer.start_month'   => 'nullable|string',
            'customer.end_month'     => 'nullable|string',

            'cart'  => 'required|array|min:1',
            'total' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator) // ✅ Proper Laravel validation error flashing
                ->withInput();           // ✅ Keep user-entered values
        }


        DB::beginTransaction();

        try {

            $customer = $payload['customer'];

            // ✅ Create order
            $order = Orders::create([
                'tid'          => Orders::max('tid') + 1,
                'customer_id'  => $customer['customer_id'],
                'order_type'   => $customer['order_type'],
                'frequency'    => $customer['order_type'] === 'recurring'
                    ? ($customer['frequency'] ?? null)
                    : null,
                'start_month'  => date_for_database($customer['start_month']) ?? null,
                'end_month'    => date_for_database($customer['end_month']) ?? null,
                'subtotal'     => $payload['total'],
                'total'        => $payload['total'],
            ]);

            // ✅ Save cart items
            foreach ($payload['cart'] as $item) {
                OrdersItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $item['id'],
                    'qty'        => $item['qty'],
                    'rate'       => $item['price'],
                    'itemtax'    => 0,
                    'amount'     => $item['qty'] * $item['price'],
                ]);
            }

            // ✅ One-time order scheduling
            if ($order->order_type === 'one_time') {
                $this->create_schedule($order, $customer);

                $order->update([
                    'status' => 'confirmed'
                ]);
            }

            DB::commit();

            return redirect()->route('biller.customer_pages.thank_you');
        } catch (\Throwable $th) {

            DB::rollBack();
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
