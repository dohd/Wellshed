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
    protected $signature = 'create:delivery_schedule';
    protected $description = 'Automatically create delivery schedules based on order frequency and type';

    // 🗓 Generate 1 month ahead
    protected $generationWindowMonths = 1;

    public function handle()
    {
        $today   = Carbon::today();
        $endDate = $today->copy()->addMonths($this->generationWindowMonths)->endOfMonth();

        $orders = Orders::whereIn('status', ['confirmed', 'started'])
            ->with(['deliver_days', 'items'])
            ->get();

        DB::beginTransaction();

        try {

            foreach ($orders as $order) {

                $startDate = Carbon::parse($order->start_month ?? $today)->startOfDay();

                if ($startDate->gt($endDate)) {
                    continue;
                }

                foreach ($order->deliver_days as $freq) {

                    $frequency = strtolower($freq->frequency);

                    // ✅ SAFE extractions
                    $deliveryDays = $this->safeArray($freq->delivery_days);
                    $weekNumbers  = $this->safeArray($freq->week_numbers);
                    $locationsMap = $this->safeAssoc($freq->locations_for_days);

                    switch ($frequency) {

                        case 'daily':
                            $this->handleDaily($order, $freq, $startDate, $endDate, $locationsMap);
                            break;

                        case 'weekly':
                            $this->handleWeekly($order, $freq, $startDate, $endDate, $deliveryDays, $locationsMap);
                            break;

                        case 'custom':
                            $this->handleCustom($order, $freq, $startDate, $endDate, $deliveryDays, $weekNumbers, $locationsMap);
                            break;

                        default:
                            continue 2;
                    }
                }
            }

            DB::commit();
            $this->info('✅ Delivery schedules generated for the next ' . $this->generationWindowMonths . ' month(s).');

        } catch (\Throwable $e) {

            DB::rollBack();
            \Log::error('❌ Error creating delivery schedules: ' . $e->getMessage());
            $this->error('Error: ' . $e->getMessage());
        }
    }

    /**
     * DAILY: generate every day
     */
    private function handleDaily($order, $freq, $startDate, $endDate, array $locationsMap)
    {
        $date = $startDate->copy();
        while ($date->lte($endDate)) {
            $dayName = strtolower($date->format('l'));
            $locationId = $locationsMap[$dayName][0] ?? null;

            $this->createScheduleIfNotExists($order, $freq, $date, $locationId);
            $date->addDay();
        }
    }

    /**
     * WEEKLY: specific weekdays
     */
    private function handleWeekly($order, $freq, $startDate, $endDate, array $deliveryDays, array $locationsMap)
    {
        $date = $startDate->copy();

        while ($date->lte($endDate)) {

            $dayName = $date->format('l');

            if (in_array($dayName, $deliveryDays)) {
                $key = strtolower($dayName);
                $locationId = $locationsMap[$key][0] ?? null;

                $this->createScheduleIfNotExists($order, $freq, $date, $locationId);
            }

            $date->addDay();
        }
    }

    /**
     * CUSTOM frequency: week numbers + weekdays
     */
    private function handleCustom($order, $freq, $startDate, $endDate, array $deliveryDays, array $weekNumbers, array $locationsMap)
    {
        $currentMonth = $startDate->copy()->startOfMonth();

        while ($currentMonth->lte($endDate)) {

            foreach ($weekNumbers as $weekNum) {

                foreach ($deliveryDays as $dayName) {

                    $targetDate = $this->getNthWeekdayOfMonth($currentMonth, $dayName, intval($weekNum));

                    if ($targetDate && $targetDate->between($startDate, $endDate)) {

                        $key = strtolower($dayName);
                        $locationId = $locationsMap[$key][0] ?? null;

                        $this->createScheduleIfNotExists($order, $freq, $targetDate, $locationId);
                    }
                }
            }

            $currentMonth->addMonth();
        }
    }

    /** Create if not exists */
    private function createScheduleIfNotExists($order, $freq, Carbon $date, $locationId = null)
    {
        $exists = DeliverySchedule::where('order_id', $order->id)
            ->whereDate('delivery_date', $date->toDateString())
            ->exists();

        if ($exists) {
            return;
        }

        $schedule = DeliverySchedule::create([
            'tid'                   => (DeliverySchedule::max('tid') ?? 0) + 1,
            'order_id'              => $order->id,
            'customer_id'           => $order->customer_id,
            'delivery_date'         => $date->toDateString(),
            'delivery_time'         => $freq->expected_time,
            'delivery_frequency_id' => $freq->id,
            'location_id'           => $locationId,
            'status'                => 'scheduled',
            'ins'                   => $order->ins,
            'user_id'               => $order->user_id,
        ]);

        // Items
        $items = $order->items->map(function ($item) use ($schedule) {
            return [
                'delivery_schedule_id' => $schedule->id,
                'order_item_id'        => $item->id,
                'product_id'           => $item->product_id,
                'qty'                  => $item->qty,
                'rate'                 => $item->rate,
                'amount'               => $item->amount,
                'ins'                  => $schedule->ins,
                'created_at'           => now(),
                'updated_at'           => now(),
            ];
        })->toArray();

        if ($items) {
            DeliveryScheduleItem::insert($items);
        }
    }

    /**
     * Find Nth weekday of a month (e.g., 2nd Monday)
     */
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

    /**
     * Convert `$value` → array (for list JSON)
     */
    private function safeArray($value): array
    {
        if (is_array($value)) return $value;
        if (is_null($value)) return [];

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    /**
     * Convert `$value` → associative array (for map JSON)
     */
    private function safeAssoc($value): array
    {
        if (is_array($value)) return $value;
        if (is_null($value)) return [];

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }
}
