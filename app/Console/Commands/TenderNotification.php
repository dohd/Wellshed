<?php

namespace App\Console\Commands;

use App\Models\Company\RecipientSetting;
use App\Models\Company\SmsSetting;
use App\Models\hrm\Hrm;
use App\Models\send_email\SendEmail;
use App\Models\send_sms\SendSms;
use App\Models\sms_response\SmsResponse;
use App\Models\tenant\Tenant;
use App\Models\tender\Tender;
use App\Repositories\Focus\general\RosemailerRepository;
use Auth;
use Carbon\Carbon;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Console\Command;

class TenderNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tender:notify_users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Users Notification On Tender Activities';

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
        foreach ($tenants as $tenant) {
            $tenders = Tender::withoutGlobalScopes()
                ->where('ins', $tenant->id)
                ->where('notify', 'yes')
                ->where(function ($query) {
                    $query->whereDate('submission_date', Carbon::today())
                        ->orWhereDate('site_visit_date', Carbon::today());
                })
                ->get();

            $submissionTenders = $tenders->where('submission_date', Carbon::today()->toDateString());
            $siteVisitTenders = $tenders->where('site_visit_date', Carbon::today()->toDateString());

            $sms_server = SmsSetting::withoutGlobalScopes()->where('ins', $tenant->id)->first();
            $setting = RecipientSetting::withoutGlobalScopes()
                ->where('ins', $tenant->id)
                ->where('type', 'tender_notification')
                ->first();

            $this->processTenders($submissionTenders, 'submission', $tenant, $sms_server, $setting);
            $this->processTenders($siteVisitTenders, 'site_visit', $tenant, $sms_server, $setting);
        }
    }

    private function processTenders($tenders, $type, $tenant, $sms_server, $setting)
    {
        if ($tenders->isEmpty()) return;

        foreach ($tenders as $tender) {
            $teamData = $this->getTeamMembers($tender->team_member_ids);
            $eventLabel = ($type == 'submission') ? 'Tender Submission Due Today' : 'Tender Site Visit Scheduled';
            $eventDate = now()->toFormattedDateString();
            $content = $this->getEmailContent($tenant->sms_email_name, $tender->title, $eventLabel, $eventDate, $type);

            if ($setting->email == 'yes') {
                $this->sendEmailNotification($teamData['emails'], $eventLabel, $content, $tender);
            }

            if ($setting->sms == 'yes' && $sms_server->active == 1) {
                $this->sendSmsNotification($teamData['contacts'], $tender->title, $tenant->sms_email_name, $tender, $tenant->id);
            }
        }
    }

    private function getTeamMembers($team_member_ids)
    {
        $ids = explode(',', $team_member_ids);
        $phone_numbers = [];
        $user_ids = [];
        $emails = [];
        $pattern = '/^(0[17]\d{8}|254[17]\d{8})$/';

        $users = Hrm::withoutGlobalScopes()->where('status', 1)->whereIn('id', $ids)->get();

        foreach ($users as $user) {
            if ($user->meta) {
                $cleanedNumber = preg_replace('/\D/', '', $user->meta->secondary_contact);
                if (preg_match($pattern, $cleanedNumber)) {
                    $formattedNumber = preg_match('/^01\d{8}$/', $cleanedNumber) ? '254' . substr($cleanedNumber, 1) : $cleanedNumber;
                    $phone_numbers[] = $formattedNumber;
                    $user_ids[] = $user->id;
                    $emails[] = $user->personal_email;
                }
            }
        }

        return [
            'contacts' => implode(',', $phone_numbers),
            'user_ids' => implode(',', $user_ids),
            'emails' => $emails,
        ];
    }

    private function getEmailContent($sender, $title, $label, $date, $type)
    {
        if ($type === 'submission') {
            return "<p>From {$sender},</p>
                    <p>This is a reminder that the <strong>{$label}</strong> for <strong>{$title}</strong> is due today, <strong>{$date}</strong>. 
                    Please ensure that all necessary documents are finalized and submitted before the deadline.</p>
                    <p>Best regards,</p>";
        } else {
            return "<p>From {$sender},</p>
                    <p>This is a reminder that the <strong>{$label}</strong> for <strong>{$title}</strong> is scheduled on <strong>{$date}</strong>.</p> 
                    <p>Please ensure you arrive on time and have all the necessary documents or equipment as required.</p>
                    <p>Best regards,</p>";
        }
    }

    private function sendEmailNotification($emails, $subject, $content, $tender)
    {
        if (empty($emails)) return;

        $mail_to = array_shift($emails);
        $email_input = [
            'text' => $content,
            'subject' => "Reminder: {$subject}",
            'email' => $emails,
            'mail_to' => $mail_to
        ];

        $email = (new RosemailerRepository($tender->ins))->send_group($email_input['text'], $email_input);
        $email_output = json_decode($email);

        if ($email_output->status === "Success") {
            SendEmail::create([
                'text_email' => $email_input['text'],
                'subject' => $email_input['subject'],
                'user_emails' => $email_input['mail_to'],
                'user_ids' => implode(',', $emails),
                'ins' => $tender->ins,
                'user_id' => $tender->user_id,
                'status' => 'sent'
            ]);
        }
    }

    private function sendSmsNotification($contacts, $title, $sender, $tender, $tenantId)
    {
        if (empty($contacts)) return;

        $date = now()->toFormattedDateString();
        $subject = "From {$sender}, reminder: Tender '{$title}' is due today ({$date}). Ensure submission before deadline.";
        $cost_per_160 = 0.6;
        $totalCharacters = strlen($subject);
        $charCount = ceil($totalCharacters / 160);
        $count_users = count(explode(',', $contacts));

        $data = [
            'subject' => $subject,
            'user_type' => 'employee',
            'delivery_type' => 'now',
            'message_type' => 'bulk',
            'phone_numbers' => $contacts,
            'sent_to_ids' => $contacts,
            'characters' => $charCount,
            'cost' => $cost_per_160,
            'user_count' => $count_users,
            'total_cost' => $cost_per_160 * $charCount * $count_users,
            'user_id' => $tender->user_id,
            'ins' => $tender->ins,
        ];

        $send_sms = new SendSms();
        $send_sms->fill($data);
        $send_sms->save();

        $this->bulk_sms($contacts, $subject, $send_sms, $tenantId);
    }

    public function bulk_sms($mobile, $text_message, $send_sms_id, $ins)
    {
        try {
            // Fetch SMS settings
            $sms_server = SmsSetting::withoutGlobalScopes()->where('ins', $ins)->first();
            if (!$sms_server) {
                \Log::error("SMS settings not found for ins: {$ins}");
                return response()->json(['status' => 'error', 'message' => 'SMS settings not found.'], 400);
            }

            // Prepare payload
            $payload = [
                'senderID' => $sms_server->sender,
                'message'  => $text_message,
                'phones'   => $mobile,
            ];

            // Send request using Guzzle
            $client = new \GuzzleHttp\Client();
            $response = $client->post('https://api.mobilesasa.com/v1/send/bulk', [
                'headers' => [
                    'Accept'        => 'application/json',
                    'Content-Type'  => 'application/json',
                    'Authorization' => 'Bearer ' . $sms_server->username,
                ],
                'json' => $payload,
            ]);

            // Parse response
            $content = json_decode($response->getBody()->getContents(), true);
            \Log::info('SMS API Response:', $content);

            // Validate response format
            if (!isset($content['bulkId'], $content['status'])) {
                \Log::error("Unexpected SMS API response format: " . json_encode($content));
                return response()->json(['status' => 'error', 'message' => 'Invalid SMS API response.'], 500);
            }

            // Save SMS response
            SmsResponse::withoutGlobalScopes()->create([
                'send_sms_id'        => $send_sms_id->id,
                'message_response_id'=> $content['bulkId'],
                'status'             => 1,
                'message_type'       => 'bulk',
                'phone_number_count' => count(explode(',', $mobile)),
                'response_code'      => $content['responseCode'] ?? null,
                'ins'                => Auth::id() ?? $ins, 
                'user_id'            => Auth::id() ?? $ins,
            ]);

            return response()->json(['status' => 'success', 'message' => 'SMS Sent Successfully.'], 200);

        } catch (\GuzzleHttp\Exception\RequestException $e) {
            \Log::error("Failed to send SMS: " . $e->getMessage(), [
                'response' => $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : null
            ]);
            return response()->json(['status' => 'error', 'message' => 'Failed to send SMS.'], 500);

        } catch (\Throwable $th) {
            \Log::error("An error occurred in bulk_sms: " . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => 'An internal error occurred.'], 500);
        }
    }

}
