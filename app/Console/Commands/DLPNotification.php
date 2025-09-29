<?php

namespace App\Console\Commands;

use App\Models\Company\RecipientSetting;
use App\Models\Company\SmsSetting;
use App\Models\hrm\Hrm;
use App\Models\job_valuation\JobValuation;
use App\Models\send_email\SendEmail;
use App\Models\send_sms\SendSms;
use App\Models\sms_response\SmsResponse;
use App\Models\tenant\Tenant;
use App\Repositories\Focus\general\RosemailerRepository;
use Carbon\Carbon;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Console\Command;

class DLPNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notify:dlp_reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'DLP Reminder Notification';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $tenants = Tenant::where('status', 'Active')->get();
        $today = Carbon::today();

        foreach ($tenants as $tenant) {
            $job_valuations = JobValuation::withoutGlobalScopes()
                ->whereDate('completion_date', '<', $today)
                ->where('ins', $tenant->id)
                ->get();

            $sms_server = SmsSetting::withoutGlobalScopes()->where('ins', $tenant->id)->first();
            $setting = RecipientSetting::withoutGlobalScopes()
                ->where('ins', $tenant->id)
                ->where('type', 'dlp_notification')
                ->first();

            if (!$setting || !$sms_server) {
                continue; // Skip if settings are missing
            }

            foreach ($job_valuations as $valuation) {
                $dlp_expiry_date = Carbon::parse($valuation->completion_date)->addDays($valuation->dlp_period * 30);
                $reminder_date = $dlp_expiry_date->copy()->subDays($valuation->dlp_reminder);

                if (!$today->equalTo($reminder_date)) {
                    continue;
                }

                $employee_ids = explode(',', $valuation->employee_ids);
                $quote = $valuation->quote()->withoutGlobalScopes()->first();
                $project = $quote->project()->withoutGlobalScopes()->first();

                if (!$quote || !$project) {
                    continue; // Skip if data is incomplete
                }

                $client = $quote->client ? $quote->client()->withoutGlobalScopes()->first() : $quote->quote_client()->withoutGlobalScopes()->first();
                $clientname = $client ? $client->company : 'Unknown Client';
                $branch = $quote->branch ? $quote->branch()->withoutGlobalScopes()->first()->name : '';
                $proj_no = gen4tid('PRJ-', $project->tid);
                $proj_name = $project->name;
                $expire_date = $dlp_expiry_date->format('F d, Y');

                $employees = Hrm::withoutGlobalScopes()->where('status', 1)->whereIn('id', $employee_ids)->get();
                $phone_numbers = [];
                $user_ids = [];
                $emails = [];
                $pattern = '/^(0[17]\d{8}|254[17]\d{8})$/';

                foreach ($employees as $user) {
                    if (!$user->meta) {
                        continue;
                    }

                    $cleanedNumber = preg_replace('/\D/', '', $user->meta->primary_contact);
                    if (!preg_match($pattern, $cleanedNumber)) {
                        continue;
                    }

                    $phone = preg_match('/^01\d{8}$/', $cleanedNumber) ? '254' . substr($cleanedNumber, 1) : $cleanedNumber;
                    $phone_numbers[] = $phone;
                    $user_ids[] = $user->id;
                    $emails[] = $user->email;

                    $subject = "From {$tenant->sms_email_name}, Dear {$user->fullname}, please note that your Defects and Liability Period for {$clientname} - {$branch} for {$proj_no}-{$proj_name} is ending on {$expire_date}. Please plan ahead. Thanks";

                    $charCount = ceil(strlen($subject) / 160);
                    $count_users = count($user_ids);
                    $cost_per_160 = 0.6;

                    $data = [
                        'subject' => $subject,
                        'user_type' => 'employee',
                        'delivery_type' => 'now',
                        'message_type' => 'bulk',
                        'phone_numbers' => $phone,
                        'sent_to_ids' => $user->id,
                        'characters' => $charCount,
                        'cost' => $cost_per_160,
                        'user_count' => $count_users,
                        'total_cost' => $cost_per_160 * $charCount * $count_users,
                        'user_id' => $valuation->user_id,
                        'ins' => $valuation->ins,
                    ];

                    if ($setting->sms == 'yes' && $sms_server->active) {
                        $send_sms = SendSms::create($data);
                        $this->bulk_sms($phone, $subject, $send_sms, $tenant->id);
                    }

                    if ($setting->email == 'yes') {
                        $email_input = [
                            'text' => "Dear {$user->fullname}, please note that your Defects and Liability Period for {$clientname} - {$branch} for {$proj_no}-{$proj_name} is ending on {$expire_date}. Please plan ahead. Thanks",
                            'subject' => 'Reminder: Defects and Liability Period',
                            'mail_to' => $user->email,
                        ];

                        $email = (new RosemailerRepository($user->ins))->send($email_input['text'], $email_input);
                        $email_output = json_decode($email);

                        if ($email_output->status === "Success") {
                            SendEmail::create([
                                'text_email' => $email_input['subject'],
                                'subject' => $email_input['subject'],
                                'user_emails' => $email_input['mail_to'],
                                'user_ids' => $user->id,
                                'ins' => $user->ins,
                                'user_id' => $user->ins,
                                'status' => 'sent'
                            ]);
                        }
                    }
                }
            }
        }
    }

    public function bulk_sms($mobile, $text_message, $send_sms_id, $ins)
    {
        $sms_server = SmsSetting::withoutGlobalScopes()->where('ins', $ins)->first();

        if (!$sms_server || !$sms_server->active) {
            return;
        }

        $payload = [
            'senderID' => $sms_server->sender,
            'message' => $text_message,
            'phones' => $mobile,
        ];

        try {
            $client = new \GuzzleHttp\Client();
            $apiToken = $sms_server->username;

            $response = $client->post('https://api.mobilesasa.com/v1/send/bulk', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $apiToken,
                ],
                'json' => $payload,
            ]);

            $content = json_decode($response->getBody()->getContents(), true);

            if (!isset($content['bulkId']) || !isset($content['status'])) {
                return;
            }

            $phone_numbers = explode(',', $mobile);

            SmsResponse::withoutGlobalScopes(['ins'])->create([
                'send_sms_id' => $send_sms_id->id,
                'message_response_id' => $content['bulkId'],
                'status' => 1,
                'message_type' => 'bulk',
                'phone_number_count' => count($phone_numbers),
                'response_code' => $content['responseCode'] ?? null,
                'ins' => $ins,
                'user_id' => auth()->id() ?? $ins,
            ]);
        } catch (RequestException $e) {
            \Log::error("Failed to send SMS: " . $e->getMessage());
        } catch (\Exception $e) {
            \Log::error("SMS Error: " . $e->getMessage());
        }
    }
}
