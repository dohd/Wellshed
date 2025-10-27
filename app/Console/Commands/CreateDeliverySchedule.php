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

    public function handle()
    {
        $today = Carbon::today();

        $orders = Orders::whereIn('status', ['confirmed', 'started'])
            ->with(['deliver_days', 'items'])
            ->get();

        DB::beginTransaction();

        try {
            foreach ($orders as $order) {
                $startDate = Carbon::parse($order->start_month)->startOfDay();
                $endDate = Carbon::parse($order->end_month)->endOfDay();

                // Skip orders entirely in the past
                if ($endDate->lt($today)) {
                    continue;
                }

                $isRecurring = strtolower($order->order_type) === 'recurring';

                foreach ($order->deliver_days as $freq) {
                    $frequency = strtolower($freq->frequency); // e.g. weekly, monthly
                    $dayName = $freq->delivery_days; // e.g. Monday, Friday
                    $expectedTime = $freq->expected_time;

                    if ($isRecurring) {
                        // Start from the first matching weekday on/after startDate or today (whichever is later)
                        $startFrom = $startDate->copy()->lt($today) ? $today->copy() : $startDate->copy();
                        $date = $this->getNextOrSameWeekday($startFrom, $dayName);

                        while ($date->lte($endDate)) {
                            // Safety: skip any past date
                            if ($date->lt($today)) {
                                // advance according to frequency then continue
                                switch ($frequency) {
                                    case 'daily':
                                        $date->addDay();
                                        break;
                                    case 'weekly':
                                        $date->addWeek();
                                        break;
                                    case 'bi-weekly':
                                    case 'biweekly':
                                        $date->addWeeks(2);
                                        break;
                                    case 'monthly':
                                        // jump to next month then find first matching weekday
                                        $nextMonth = $date->copy()->addMonth()->startOfMonth();
                                        $date = $this->getNextOrSameWeekday($nextMonth, $dayName);
                                        break;
                                    default:
                                        $date->addWeek();
                                        break;
                                }
                                continue;
                            }

                            $this->createScheduleIfNotExists($order, $freq, $date);

                            // Advance according to frequency
                            switch ($frequency) {
                                case 'daily':
                                    $date->addDay();
                                    break;

                                case 'weekly':
                                    $date->addWeek();
                                    break;

                                case 'bi-weekly':
                                case 'biweekly':
                                    $date->addWeeks(2);
                                    break;

                                case 'monthly':
                                    // go to the first matching weekday in the next month
                                    $nextMonth = $date->copy()->addMonth()->startOfMonth();
                                    $date = $this->getNextOrSameWeekday($nextMonth, $dayName);
                                    break;

                                default:
                                    $date->addWeek();
                                    break;
                            }
                        }

                    } else {
                        // ONE-TIME orders: schedule once on the next matching weekday (>= start or today)
                        $startFrom = $startDate->copy()->lt($today) ? $today->copy() : $startDate->copy();
                        $targetDate = $this->getNextOrSameWeekday($startFrom, $dayName);

                        if ($targetDate->lte($endDate)) {
                            $this->createScheduleIfNotExists($order, $freq, $targetDate);
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

    /**
     * Create schedule safely if not already existing
     */
    private function createScheduleIfNotExists($order, $freq, Carbon $date)
    {
        // Ensure we only create for today or future
        if ($date->lt(Carbon::today())) {
            return;
        }

        $exists = DeliverySchedule::where('order_id', $order->id)
            ->where('delivery_frequency_id', $freq->id)
            ->whereDate('delivery_date', $date->toDateString())
            ->exists();

        if ($exists) return;

        $schedule = DeliverySchedule::create([
            'tid' => (DeliverySchedule::max('tid') ?? 0) + 1,
            'order_id' => $order->id,
            'customer_id' => $order->customer_id,
            'delivery_date' => $date->toDateString(),
            'delivery_time' => $freq->expected_time,
            'delivery_frequency_id' => $freq->id,
            'status' => 'scheduled',
            'ins' => $order->ins,
            'user_id' => $order->user_id,
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
    }

    /**
     * Return the first date on or after $start that matches $dayName (e.g., "Monday").
     */
    private function getNextOrSameWeekday(Carbon $start, string $dayName): Carbon
    {
        $dayName = ucfirst(strtolower($dayName));
        $date = $start->copy();

        // Loop up to 14 times as a safety guard (should break far sooner)
        $attempts = 0;
        while ($date->format('l') !== $dayName && $attempts < 14) {
            $date->addDay();
            $attempts++;
        }

        return $date;
    }
}
