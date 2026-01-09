<?php

namespace App\Console\Commands;

use App\Models\delivery_schedule\DeliverySchedule;
use App\Models\delivery_schedule\DeliveryScheduleItem;
use App\Models\orders\Orders;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateDeliverySchedule extends Command
{
    protected $signature = 'create:delivery_schedule';
    protected $description = 'Automatically create delivery schedules based on order frequency and type';

    protected $generationWindowMonths = 1;

    public function handle()
    {
        $today = Carbon::today();
        $orders = Orders::whereIn('status', ['confirmed', 'started'])
            ->with(['deliver_days', 'items', 'customer.subscriptions'])
            ->get();

        DB::beginTransaction();

        try {
            foreach ($orders as $order) {

                $activeSubscription = $order->customer->subscriptions
                    ->where('status', 'active')
                    ->first();

                $startDate = Carbon::parse($order->start_month ?? $today)->startOfDay();
                $endDate = $activeSubscription
                    ? Carbon::parse($activeSubscription->end_date)->endOfDay()
                    : $today->copy()->addMonths($this->generationWindowMonths)->endOfMonth();


                if ($startDate->gt($endDate)) {
                    Log::warning("Skipping Order ID {$order->id} because start date is after end date.");
                    continue;
                }

                foreach ($order->deliver_days as $freq) {
                    $frequency = strtolower($freq->frequency);

                    $deliveryDays = $this->safeArray($freq->delivery_days);
                    $weekNumbers  = $this->safeArray($freq->week_numbers);
                    $locationsMap = $this->safeAssoc($freq->locations_for_days);
                    $qtyPerDay    = $this->safeAssoc($freq->qty_per_day);


                    switch ($frequency) {
                        case 'daily':
                            $this->handleDaily($order, $freq, $startDate, $endDate, $locationsMap, $qtyPerDay);
                            break;

                        case 'weekly':
                            $this->handleWeekly($order, $freq, $startDate, $endDate, $deliveryDays, $locationsMap, $qtyPerDay);
                            break;

                        case 'custom':
                            $this->handleCustom($order, $freq, $startDate, $endDate, $deliveryDays, $weekNumbers, $locationsMap, $qtyPerDay);
                            break;

                        default:
                            Log::warning("Unknown frequency '{$frequency}' for Order ID {$order->id}");
                            continue 2;
                    }
                }
            }

            DB::commit();
            $this->info('✅ Delivery schedules generated successfully');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('❌ Error creating delivery schedules: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            $this->error('Error: ' . $e->getMessage());
        }
    }

    private function handleDaily($order, $freq, $startDate, $endDate, array $locationsMap, array $qtyPerDay)
    {
        $date = $startDate->copy();
        while ($date->lte($endDate)) {
            $dayName = strtolower($date->format('l'));
            $locationId = $locationsMap[$dayName][0] ?? null;

            $qty = intval($qtyPerDay[$dayName] ?? 0);
            if ($qty > 0) {
                $this->createSchedule($order, $freq, $date, $locationId, $qty);
            }

            $date->addDay();
        }
    }

    private function handleWeekly($order, $freq, $startDate, $endDate, array $deliveryDays, array $locationsMap, array $qtyPerDay)
    {
        $date = $startDate->copy();
        while ($date->lte($endDate)) {
            $dayNameFull = $date->format('l');
            if (in_array($dayNameFull, $deliveryDays)) {
                $dayKey = strtolower($dayNameFull);
                $locationId = $locationsMap[$dayKey][0] ?? null;
                $key = "qty_for_day[{$dayKey}]";
                $qty = intval($qtyPerDay[$key] ?? 0);

                // dd($dayNameFull,$qty);
                if ($qty > 0) {
                    $this->createSchedule($order, $freq, $date, $locationId, $qty);
                }
            }
            $date->addDay();
        }
    }

    private function handleCustom($order, $freq, $startDate, $endDate, array $deliveryDays, array $weekNumbers, array $locationsMap, array $qtyPerDay)
    {
        $currentMonth = $startDate->copy()->startOfMonth();
        while ($currentMonth->lte($endDate)) {
            foreach ($weekNumbers as $weekNum) {
                foreach ($deliveryDays as $dayName) {
                    $targetDate = $this->getNthWeekdayOfMonth($currentMonth, $dayName, intval($weekNum));
                    if ($targetDate && $targetDate->between($startDate, $endDate)) {
                        $dayKey = strtolower($dayName);
                        $locationId = $locationsMap[$dayKey][0] ?? null;

                        // Parse qty_per_day key
                        $key = "qty_for_custom[{$dayKey}][{$weekNum}]";
                        $weekQty = intval($qtyPerDay[$key] ?? 0);


                        if ($weekQty > 0) {
                            $this->createSchedule($order, $freq, $targetDate, $locationId, $weekQty);
                        }
                    }
                }
            }
            $currentMonth->addMonth();
        }
    }

    private function createSchedule($order, $freq, Carbon $date, $locationId = null, int $qty = 0)
    {
        $schedule = DeliverySchedule::firstOrCreate(
            ['order_id' => $order->id, 'delivery_date' => $date->toDateString()],
            [
                'tid' => (DeliverySchedule::max('tid') ?? 0) + 1,
                'customer_id' => $order->customer_id,
                'delivery_time' => $freq->expected_time,
                'delivery_frequency_id' => $freq->id,
                'location_id' => $locationId,
                'status' => 'scheduled',
                'ins' => $order->ins,
                'user_id' => $order->user_id,
            ]
        );

        DeliveryScheduleItem::where('delivery_schedule_id', $schedule->id)->delete();

        $items = $order->items->map(function ($item) use ($schedule, $qty) {
            return [
                'delivery_schedule_id' => $schedule->id,
                'order_item_id' => $item->id,
                'product_id' => $item->product_id,
                'qty' => $qty,
                'rate' => $item->rate,
                'amount' => $item->rate * $qty,
                'ins' => $schedule->ins,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        })->toArray();

        if ($items) {
            DeliveryScheduleItem::insert($items);
        }
    }

    private function getNthWeekdayOfMonth(Carbon $startOfMonth, string $dayName, int $weekNum): ?Carbon
    {
        $dayName = ucfirst(strtolower(trim($dayName)));
        $date = $startOfMonth->copy();
        while ($date->format('l') !== $dayName) {
            $date->addDay();
        }
        $target = $date->copy()->addWeeks($weekNum - 1);
        return $target->month === $startOfMonth->month ? $target : null;
    }

    private function safeArray($value): array
    {
        if (is_array($value)) return $value;
        if (is_null($value)) return [];
        $decoded = json_decode($value, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function safeAssoc($value): array
    {
        return $this->safeArray($value);
    }
}
