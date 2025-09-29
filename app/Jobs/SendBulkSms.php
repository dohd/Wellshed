<?php

namespace App\Jobs;

use App\Models\send_sms\SendSms;
use App\Repositories\Focus\general\RosesmsRepository;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class SendBulkSms implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    protected $ins;
    protected $phone;
    protected $message;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($ins, $phone, $message)
    {
        $this->ins = $ins;
        $this->phone = $phone;
        $this->message = $message;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            // Assumes 'ins' is on the user model
            $ins = $this->ins;
            // dd($this->phone);

            // === SEND SMS ===
            if ($this->phone) {
                DB::beginTransaction();

                $charCount = strlen($this->message);
                $cost_per_160 = 0.6;

                $sms = SendSms::create([
                    'subject' => $this->message,
                    'phone_numbers' => $this->phone,
                    'user_type' => 'customer',
                    'delivery_type' => 'now',
                    'message_type' => 'single',
                    'sent_to_ids' => '',
                    'characters' => $charCount,
                    'cost' => $cost_per_160,
                    'user_count' => 1,
                    'total_cost' => $cost_per_160 * ceil($charCount / 160),
                    'user_id' => $ins,
                    'ins' => $ins,
                ]);

                (new RosesmsRepository($ins))->bulk_sms($this->phone, $this->message, $sms);

                DB::commit();

                // ✅ Log success
                \Log::info('SendBulkSms Job Success', [
                    'phone'   => $this->phone,
                    'message' => $this->message,
                    'length'  => $charCount,
                    'cost'    => $cost_per_160 * ceil($charCount / 160),
                    'sms_id'  => $sms->id ?? null,
                    'ins'     => $ins,
                ]);
            }
        } catch (Exception $ex) {
            DB::rollBack();
            \Log::error('SendBulkSms Job Error', [
                'message' => $ex->getMessage(),
                'code'    => $ex->getCode(),
                'file'    => $ex->getFile(),
                'line'    => $ex->getLine(),
            ]);
        }
    }
}
