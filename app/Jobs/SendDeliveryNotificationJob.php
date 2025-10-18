<?php

namespace App\Jobs;

use App\Models\delivery\Delivery;
use App\Repositories\AdvantaSmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendDeliveryNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $deliveryId;

    /**
     * Create a new job instance.
     */
    public function __construct($deliveryId)
    {
        $this->deliveryId = $deliveryId;
    }

    /**
     * Execute the job.
     */
    public function handle(AdvantaSmsService $smsService)
    {
        Log::info('🚚 [SendDeliveryNotificationJob] Started', [
            'delivery_id' => $this->deliveryId,
            'time' => now()->toDateTimeString(),
        ]);

        try {
            // 🧩 Fetch delivery without global scopes
            $delivery = Delivery::withoutGlobalScopes()
                ->with([
                    'customer' => fn($q) => $q->withoutGlobalScopes(),
                    'driver' => fn($q) => $q->withoutGlobalScopes(['ins'])->with('meta'),
                    'delivery_schedule' => fn($q) => $q->withoutGlobalScopes(),
                    'order' => fn($q) => $q->withoutGlobalScopes(),
                ])
                ->find($this->deliveryId);

            if (!$delivery) {
                Log::warning('⚠️ Delivery not found', ['delivery_id' => $this->deliveryId]);
                return;
            }

            if (strtolower($delivery->delivery_schedule->status) !== 'delivered') {
                Log::info('ℹ️ Delivery status not completed — skipping SMS', [
                    'delivery_id' => $delivery->id,
                    'status' => $delivery->status,
                ]);
                return;
            }

            $details = [
                'customer_name' => $delivery->customer->name ?? '',
                'order_no'      => $delivery->order ? gen4tid('ORD-', $delivery->order->tid) : '',
                'delivery_date' => optional($delivery->delivery_schedule)->delivery_date ?? '',
                'status'        => ucfirst($delivery->status),
            ];

            // Normalize phone numbers (you can replace with normalize_phone_number helper)
            $customer_phone = normalize_phone_number(optional($delivery->customer)->phone);
            $driver_phone   = normalize_phone_number(optional(optional($delivery->driver)->meta)->primary_contact);
            $manager_phone  = normalize_phone_number(optional(optional($delivery->delivery_schedule)->dispatched_by_meta)->phone);

            Log::info('📞 Normalized phone numbers', [
                'customer_phone' => $customer_phone,
                'manager_phone' => $manager_phone,
                'driver_phone' => $driver_phone,
            ]);

            // 1️⃣ Customer SMS
            if ($customer_phone) {
                $response = $smsService->send(
                    $customer_phone,
                    "Hi {$details['customer_name']}, your order #{$details['order_no']} has been successfully delivered on {$details['delivery_date']}. Thank you for choosing us!"
                );
                Log::info('📨 Sent customer SMS', [
                    'phone' => $customer_phone,
                    'response' => $response,
                ]);
            }

            // 2️⃣ Store Manager SMS
            if ($manager_phone) {
                $response = $smsService->send(
                    $manager_phone,
                    "Delivery completed: Order #{$details['order_no']} ({$details['customer_name']}) was successfully delivered on {$details['delivery_date']}."
                );
                Log::info('🏢 Sent manager SMS', [
                    'phone' => $manager_phone,
                    'response' => $response,
                ]);
            }

            // 3️⃣ Driver SMS
            if ($driver_phone) {
                $response = $smsService->send(
                    $driver_phone,
                    "Good job! You've completed delivery for Order #{$details['order_no']} ({$details['customer_name']}) on {$details['delivery_date']}."
                );
                Log::info('🚚 Sent driver SMS', [
                    'phone' => $driver_phone,
                    'response' => $response,
                ]);
            }

            Log::info('✅ [SendDeliveryNotificationJob] Completed successfully', [
                'delivery_id' => $this->deliveryId,
                'status' => $delivery->status,
            ]);
        } catch (\Exception $e) {
            Log::error('❌ [SendDeliveryNotificationJob] Failed', [
                'delivery_id' => $this->deliveryId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
