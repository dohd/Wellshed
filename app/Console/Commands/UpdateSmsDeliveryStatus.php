<?php

namespace App\Console\Commands;

use App\Models\sms_log\SmsLog;
use App\Repositories\AdvantaSmsService;
use Illuminate\Console\Command;

class UpdateSmsDeliveryStatus extends Command
{
    protected $signature = 'sms:update-status';
    protected $description = 'Check and update SMS delivery status from AdvantaSMS';

    protected $smsService;

    public function __construct(AdvantaSmsService $smsService)
    {
        parent::__construct();
        $this->smsService = $smsService;
    }

    public function handle()
    {
        // Get all pending or sent messages (not yet delivered or failed)
        $pendingMessages = SmsLog::whereIn('status', ['pending', 'sent'])->get();

        foreach ($pendingMessages as $log) {
            if (!$log->message_id) continue;

            $this->info("Checking message ID: {$log->message_id}");

            $result = $this->smsService->checkDeliveryStatus($log->message_id);

            // Example response: ['responses' => [['status' => 'DELIVRD']]]
            $newStatus = $result['responses'][0]['status'] ?? null;

            if ($newStatus) {
                $log->update([
                    'status' => strtolower($newStatus),
                    'response' => json_encode($result),
                ]);
                $this->info("Updated {$log->mobile} → {$newStatus}");
            } else {
                $this->warn("No status for {$log->message_id}");
            }
        }

        $this->info("✅ SMS delivery status check completed.");
    }
}
