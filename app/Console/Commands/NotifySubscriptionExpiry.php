<?php

namespace App\Console\Commands;

use App\Models\customer\Customer;
use App\Models\send_email\SendEmail;
use App\Models\subscription\Subscription;
use App\Repositories\AdvantaSmsService;
use App\Repositories\Focus\general\RosemailerRepository;
use App\Services\SmsService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class NotifySubscriptionExpiry extends Command
{
    protected $signature = 'subscriptions:notify-expiry 
                            {--dry : Log actions without sending}';

    protected $description = 'Notify customers via SMS and Email 3 days before end_date and on the end_date';

    public function handle()
    {
        $tz = config('app.timezone', 'Africa/Nairobi');
        $today = Carbon::today($tz);
        $tMinus3 = (clone $today)->addDays(3);

        $dry = (bool) $this->option('dry');

        $this->info("Subscriptions notifications for: today={$today->toDateString()}, T-3={$tMinus3->toDateString()} [dry=" . ($dry ? 'yes' : 'no') . "]");

        // Expiring in 3 days, not yet notified
        $expiring = Subscription::query()
            ->where('status', 'active')
            ->whereDate('end_date', $tMinus3->toDateString())
            ->where('notified_expiring', false)
            ->orderBy('id');

        // Expired today, not yet notified
        $expiringToday = Subscription::query()
            ->whereIn('status', ['active', 'expired'])
            ->whereDate('end_date', $today->toDateString())
            ->where('notified_expired', false)
            ->orderBy('id');

        $expiringCount = $this->process($expiring, 'expiring', $dry);
        $expiredCount  = $this->process($expiringToday, 'expired',  $dry);

        $this->info("Done. Sent => expiring(T-3): {$expiringCount['sent']}, expired(T0): {$expiredCount['sent']}.");

        return 0;
    }

    protected function process($query, string $mode, bool $dry): array
    {
        $sent = 0;
        $skipped = 0;

        $query->chunkById(200, function ($subs) use (&$sent, &$skipped, $mode, $dry) {
            foreach ($subs as $sub) {
                /** @var Customer|null $customer */
                $customer = Customer::find($sub->customer_id);
                if (!$customer) {
                    $this->line("… sub#{$sub->id} has no customer -> skip");
                    $skipped++;
                    continue;
                }

                // Build message content
                $packageName = $sub->package
                    ? (gen4tid('PKG-', $sub->package->tid).' - '.$sub->package->name ?? "#{$sub->sub_package_id}")
                    : "#{$sub->sub_package_id}";

                $customerName = trim(($customer->name ?? $customer->company ?? 'Customer'));
                $endDateStr   = \Carbon\Carbon::parse($sub->end_date)
                    ->timezone(config('app.timezone', 'Africa/Nairobi'))
                    ->toDayDateTimeString();

                if ($mode === 'expiring') {
                    $emailSubject = 'Your subscription expires in 3 days';
                    $emailBody = "Hello {$customerName},\n\n"
                        . "This is a friendly reminder that your subscription (Package: {$packageName}) "
                        . "will expire in 3 days on {$endDateStr}.\n\n"
                        . "Kindly renew to avoid service interruption.\n\n"
                        . "Thank you.";
                    $smsText = "Hi {$customerName}, your subscription ({$packageName}) expires in 3 days on {$endDateStr}. Please renew to avoid interruption.";
                } else {
                    $emailSubject = 'Your subscription has expired';
                    $emailBody = "Hello {$customerName},\n\n"
                        . "Your subscription (Package: {$packageName}) reached its end date on {$endDateStr} and is now expired.\n\n"
                        . "Please renew to continue enjoying our services.\n\n"
                        . "If you believe this is an error, kindly contact support.";
                    $smsText = "Hi {$customerName}, your subscription ({$packageName}) expired on {$endDateStr}. Please renew to continue service.";
                }

                $customerPhone = normalize_phone_number($customer->phone) ?? $customer->mobile ?? null;
                $customerEmail = $customer->email ?? null;
                $tenantId = $sub->ins ?? null;

                if ($dry) {
                    $this->line("[DRY] sub#{$sub->id} {$mode} -> phone: " . ($customerPhone ?: '-') . ", email: " . ($customerEmail ?: '-'));
                    $sent++;
                    continue;
                }

                DB::beginTransaction();
                try {
                    // Send SMS
                    if ($customerPhone) {
                        /** @var AdvantaSmsService $smsService */
                        $smsService = app(AdvantaSmsService::class);
                        $smsService->send($customerPhone, $smsText);
                    }

                    // Send Email
                    if ($customerEmail && $tenantId) {
                        $email_input = [
                            'text'    => $emailBody,
                            'subject' => $emailSubject,
                            'mail_to' => $customerEmail,
                        ];

                        /** @var RosemailerRepository $mailer */
                        $mailer = new RosemailerRepository($tenantId);
                        $email  = $mailer->send($email_input['text'], $email_input);

                        $email_output = json_decode($email);
                        if (($email_output->status ?? null) === "Success") {
                            SendEmail::create([
                                'text_email'  => $email_input['text'],
                                'subject'     => $email_input['subject'],
                                'user_emails' => $email_input['mail_to'],
                                'user_ids'    => $customer->id,
                                'ins'         => $tenantId,
                                'user_id'     => $tenantId,
                                'status'      => 'sent',
                            ]);
                        }
                    }

                    // Mark as notified
                    if ($mode === 'expiring') {
                        $sub->notified_expiring = true;
                    } else {
                        
                        $sub->notified_expired = true;
                        if ($sub->status !== 'expired') {
                            $sub->status = 'expired';
                        }
                    }

                    $sub->save();
                    DB::commit();

                    $sent++;
                } catch (\Throwable $e) {
                    DB::rollBack();
                    $this->error("Error on sub#{$sub->id} ({$mode}): " . $e->getMessage());
                    $skipped++;
                }
            }
        });

        return compact('sent', 'skipped');
    }
}
