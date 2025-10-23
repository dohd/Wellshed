<?php

namespace App\Jobs;

use App\Models\delivery_schedule\DeliverySchedule;
use App\Repositories\AdvantaSmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendEnRouteNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $scheduleId;

    /**
     * Create a new job instance.
     */
    public function __construct($scheduleId)
    {
        $this->scheduleId = $scheduleId;
    }

    /**
     * Execute the job.
     */
    public function handle(AdvantaSmsService $smsService)
    {
        Log::info('🚚 [SendEnRouteNotificationJob] Started', [
            'schedule_id' => $this->scheduleId,
            'time' => now()->toDateTimeString(),
        ]);

        try {
            // Load schedule and relationships
            $schedule = DeliverySchedule::withoutGlobalScopes()
                ->with([
                    'order.customer' => fn($q) => $q->withoutGlobalScopes(),
                    'items.product' => fn($q) => $q->withoutGlobalScopes(), // ✅ include items
                    'store_manager' => fn($q) => $q->withoutGlobalScopes(),
                ])
                ->find($this->scheduleId);

            if (!$schedule) {
                Log::warning('⚠️ Delivery schedule not found', ['schedule_id' => $this->scheduleId]);
                return;
            }

            if (strtolower($schedule->status) !== 'en_route') {
                Log::info('ℹ️ Schedule not en_route — skipping SMS', [
                    'status' => $schedule->status,
                ]);
                return;
            }

            $customer = optional($schedule->order)->customer;
            $manager  = $schedule->store_manager;

            $order_no = $schedule->order ? gen4tid('ORD-', $schedule->order->tid) : 'N/A';
            $delivery_date = $schedule->delivery_date ?? now()->toDateString();

            // ✅ Format item list for SMS
            $itemsList = '';
            if ($schedule->order && $schedule->items->count()) {
                $items = $schedule->items->map(function ($item) {
                    $name = optional($item->product)->name ?? 'Item';
                    $qty  = $item->qty ?? 1;
                    return "{$name} ({$qty})";
                })->toArray();

                // Limit items for SMS length
                $itemsList = implode(', ', array_slice($items, 0, 4));
                if (count($items) > 4) {
                    $itemsList .= '...';
                }
            }

            // Normalize phone numbers
            $customer_phone = normalize_phone_number(optional($customer)->phone);
            $manager_phone  = normalize_phone_number(optional($manager)->meta->primary_contact);

            Log::info('📞 Normalized phone numbers', [
                'customer_phone' => $customer_phone,
                'manager_phone'  => $manager_phone,
            ]);

            // 🧩 Customer SMS
            if ($customer_phone) {
                $message = "Hi {$customer->name}, your order #{$order_no} containing {$itemsList} is now en route. Expected delivery: {$delivery_date}.";
                $response = $smsService->send($customer_phone, $message);

                Log::info('📨 Sent customer SMS', [
                    'phone' => $customer_phone,
                    'response' => $response,
                    'message' => $message,
                ]);
            }

            // 🧩 Store Manager SMS
            if ($manager_phone) {
                $message = "Order #{$order_no} ({$customer->name}) with items: {$itemsList} is now en route. Expected delivery: {$delivery_date}.";
                $response = $smsService->send($manager_phone, $message);

                Log::info('🏢 Sent store manager SMS', [
                    'phone' => $manager_phone,
                    'response' => $response,
                    'message' => $message,
                ]);
            }

            Log::info('✅ [SendEnRouteNotificationJob] Completed successfully', [
                'schedule_id' => $this->scheduleId,
            ]);
        } catch (\Exception $e) {
            Log::error('❌ [SendEnRouteNotificationJob] Failed', [
                'schedule_id' => $this->scheduleId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
