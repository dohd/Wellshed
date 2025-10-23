<?php

namespace App\Jobs;

use App\Models\Company\Company;
use App\Models\delivery\Delivery;
use App\Repositories\Focus\general\RosemailerRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendDeliveryEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $deliveryId;
    protected $ins;

    /**
     * Create a new job instance.
     */
    public function __construct($deliveryId, $ins)
    {
        $this->deliveryId = $deliveryId;
        $this->ins = $ins;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        Log::info('📧 [SendDeliveryEmailJob] Started', [
            'delivery_id' => $this->deliveryId,
            'time' => now()->toDateTimeString(),
        ]);

        try {
            $delivery = Delivery::withoutGlobalScopes()
                ->with([
                    'order',
                    'items.product',
                    'customer',
                    'delivery_schedule',
                    'delivery_schedule.store_manager',
                ])
                ->find($this->deliveryId);

            if (!$delivery) {
                Log::warning('⚠️ Delivery not found', ['delivery_id' => $this->deliveryId]);
                return;
            }

            if (strtolower($delivery->delivery_schedule->status) !== 'delivered') {
                Log::info('ℹ️ Delivery not delivered — skipping email', [
                    'status' => $delivery->delivery_schedule->status
                ]);
                return;
            }

            $customer = $delivery->customer;
            $manager = optional($delivery->delivery_schedule->store_manager);
            $company = Company::find($this->ins);

            // 🧾 Build delivered items table
            $itemsHtml = '<table width="100%" style="border-collapse: collapse; margin-top: 10px;">
                <thead>
                    <tr style="background-color:#f5f5f5; font-weight:bold;">
                        <th style="padding:8px; border:1px solid #ddd;">Item</th>
                        <th style="padding:8px; border:1px solid #ddd;">Quantity Delivered</th>
                        <th style="padding:8px; border:1px solid #ddd;">Returned Bottle</th>
                    </tr>
                </thead><tbody>';

            foreach ($delivery->items as $item) {
                $itemsHtml .= "
                    <tr>
                        <td style='padding:8px; border:1px solid #ddd;'>"
                            . e($item->product->name ?? '-') .
                        "</td>
                        <td style='padding:8px; border:1px solid #ddd; text-align:center;'>"
                            . e($item->delivered_qty ?? 0) .
                        "</td>
                        <td style='padding:8px; border:1px solid #ddd; text-align:center;'>"
                            . e($item->returned_qty ?? '-') .
                        "</td>
                    </tr>";
            }

            $itemsHtml .= '</tbody></table>';

            // 🖼️ Letterhead image

            $tid = gen4tid('ORD-',$delivery->order->tid);
            // 📧 Email body (HTML)
            $body = "
                <div style='font-family: Arial, sans-serif; color: #333; font-size: 15px; line-height: 22px;'>
                    <p>Dear {$customer->name},</p>
                    <p>We’re pleased to inform you that your order <strong>#{$tid}</strong> has been successfully delivered.</p>
                    <p><strong>Delivery Details:</strong></p>
                    {$itemsHtml}
                    <p>Delivered on: <strong>{$delivery->delivery_schedule->delivery_date}</strong></p>
                    <p>Thank you for choosing <strong>{$company->cname}</strong>!</p>
                    
                </div>
            ";

            $subject = "Order #{$delivery->order->tid} Delivered Successfully";

            // Recipients: customer and store manager
            $recipients = array_filter([
                optional($customer)->email,
                optional($manager)->email,
            ]);

            $mailer = new RosemailerRepository($company->id);

            foreach ($recipients as $recipient) {
                $input = [
                    'subject' => $subject,
                    'mail_to' => $recipient,
                    'customer_name' => $customer->name,
                ];

                $mailer->send($body, $input, 'focus.mailable.bill');

                Log::info('📨 [SendDeliveryEmailJob] Email sent', [
                    'delivery_id' => $this->deliveryId,
                    'email' => $recipient,
                ]);
            }

            Log::info('✅ [SendDeliveryEmailJob] Completed successfully', [
                'delivery_id' => $this->deliveryId,
            ]);

        } catch (\Exception $e) {
            Log::error('❌ [SendDeliveryEmailJob] Failed', [
                'delivery_id' => $this->deliveryId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
