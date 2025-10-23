<?php

namespace App\Jobs;

use App\Models\Company\Company;
use App\Models\Company\RecipientSetting;
use App\Models\delivery_schedule\DeliverySchedule;
use App\Models\send_email\SendEmail;
use App\Repositories\Focus\general\RosemailerRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendDeliveryStatusEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $schedule;
    protected $ins;

    public function __construct(DeliverySchedule $schedule, $ins)
    {
        $this->schedule = $schedule;
        $this->ins = $ins;
    }

    public function handle()
    {
        try {
            $schedule = $this->schedule->load(['order','items.product', 'order.customer', 'store_manager']);
            $order = $schedule->order;
            $customer = $schedule->order->customer;
            $store_manager = $schedule->store_manager;
            $company = Company::where('id', $this->ins)->first(); // tenant-aware helper
            $order_no = gen4tid('ORD-',$order->tid);

            if (!$customer) {
                Log::warning('Delivery email skipped: no customer found for schedule ID ' . $schedule->id);
                return;
            }

            // ✅ Build the delivery items table
            $itemsHtml = '<table width="100%" style="border-collapse: collapse; margin-top: 15px;">
                <thead>
                    <tr style="background-color:#f4f4f4; font-weight:bold;">
                        <td style="padding:8px; border:1px solid #ddd;">Item</td>
                        <td style="padding:8px; border:1px solid #ddd;">Quantity</td>
                    </tr>
                </thead><tbody>';

            foreach ($schedule->items as $item) {
                $itemsHtml .= "<tr>
                    <td style='padding:8px; border:1px solid #ddd;'>" . e($item->product->name ?? 'N/A') . "</td>
                    <td style='padding:8px; border:1px solid #ddd; text-align:center;'>" . e($item->qty ?? 0) . "</td>
                </tr>";
            }

            $itemsHtml .= '</tbody></table>';

            // ✅ Customer email
            $customer_body = "
                <p>Dear {$customer->name},</p>
                <p>Your order <strong>{$order_no}</strong> is now <strong>en route</strong> and will be delivered shortly.</p>
                <p><strong>Delivery Details:</strong></p>
                {$itemsHtml}
                <p>Thank you for choosing {$company->cname}!</p>
                <p>Best regards,<br>{$company->cname}</p>
            ";

            $this->sendEmail([
                'text' => $customer_body,
                'subject' => 'Your Order is En Route',
                'mail_to' => $customer->email,
                'customer_name' => $customer->name,
            ], ['meta' => ['user_id' => $customer->id]]);

            // ✅ Store Manager email
            if ($store_manager && $store_manager->email) {
                $manager_body = "
                    <p>Dear {$store_manager->fullname},</p>
                    <p>The following order <strong>{$order_no}</strong> has been dispatched and is now <strong>en route</strong>:</p>
                    <p>Customer: {$customer->name}<br>Email: {$customer->email}</p>
                    {$itemsHtml}
                    <p>Please ensure safe and timely delivery.</p>
                    <p>Best regards,<br>{$company->cname}</p>
                ";

                $this->sendEmail([
                    'text' => $manager_body,
                    'subject' => 'Delivery Dispatched - Order in Transit',
                    'mail_to' => $store_manager->email,
                    'customer_name' => $store_manager->fullname,
                ], ['meta' => ['user_id' => $store_manager->id]]);
            }

        } catch (\Exception $e) {
            Log::error('SendDeliveryStatusEmail failed: ' . $e->getMessage() .' Line ' .$e->getLine());
        }
    }

    protected function sendEmail($email_input, $user)
    {
        try {
            $email = (new RosemailerRepository($this->ins))
                ->send($email_input['text'], $email_input);

            $email_output = json_decode($email);
            if ($email_output->status === "Success") {
                SendEmail::create([
                    'text_email' => $email_input['text'],
                    'subject' => $email_input['subject'],
                    'user_emails' => $email_input['mail_to'],
                    'user_ids' => $user['meta']['user_id'],
                    'user_type' => 'employee',
                    'delivery_type' => 'now',
                    'status' => 'sent'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Email sending failed in job: ' . $e->getMessage());
        }
    }
}
