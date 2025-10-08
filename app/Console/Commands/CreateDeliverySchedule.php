<?php

namespace App\Console\Commands;

use App\Models\delivery_schedule\DeliverySchedule;
use App\Models\delivery_schedule\DeliveryScheduleItem;
use App\Models\orders\Orders;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreateDeliverySchedule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:delivery_schedule';

    /**
     * The console Creating Delivery Schedule.
     *
     * @var string
     */
    protected $description = 'Creating Delivery Schedule';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $today = Carbon::today();
        $orders = Orders::whereIn('status', ['confirmed', 'started'])
            ->with(['deliver_days', 'items'])
            ->get();

        DB::beginTransaction();

        try {
            foreach ($orders as $order) {
                // Ensure we have valid date range
                $startDate = Carbon::parse($order->start_month)->startOfDay();
                $endDate = Carbon::parse($order->end_month)->endOfDay();

                // Loop through each day within the order period
                for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                    $dayName = $date->format('l'); // e.g. Monday

                    // Get delivery frequencies for that weekday
                    $deliveries = $order->deliver_days->where('delivery_days', $dayName);

                    foreach ($deliveries as $freq) {
                        // Avoid duplicates — only create if not already scheduled
                        $exists = DeliverySchedule::where('order_id', $order->id)
                            ->where('delivery_frequency_id', $freq->id)
                            ->whereDate('delivery_date', $date->toDateString())
                            ->exists();

                        if ($exists) {
                            continue;
                        }

                        // ✅ Create delivery schedule
                        $schedule = DeliverySchedule::create([
                            'tid' => DeliverySchedule::max('tid')+1,
                            'order_id' => $order->id,
                            'delivery_date' => $date->toDateString(),
                            'delivery_time' => $freq->expected_time,
                            'delivery_frequency_id' => $freq->id,
                            'status' => 'scheduled',
                            'ins' => $order->ins,
                            'user_id' => $order->user_id,
                        ]);

                        // Prepare delivery schedule items
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

                        // Bulk insert items
                        if (!empty($items)) {
                            DeliveryScheduleItem::insert($items);
                        }
                    }
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Error scheduling deliveries: ' . $e->getMessage());
            throw $e;
        }
    }
}
