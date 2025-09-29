<?php

namespace App\Console\Commands;

use App\Models\Company\RecipientSetting;
use App\Models\Company\SmsSetting;
use App\Models\documentManager\DocumentManager;
use App\Models\hrm\Hrm;
use App\Models\send_sms\SendSms;
use App\Models\sms_response\SmsResponse;
use App\Models\tenant\Tenant;
use Carbon\Carbon;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Console\Command;

class DocumentTrackerNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'document:tracker_expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Document Tracker Expiry';

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
        $tenants = Tenant::all();
        $today = Carbon::today();
        foreach ($tenants as $tenant) {
            $document_trackers = DocumentManager::withoutGlobalScopes()->where('renewal_date', $today)
            ->orWhere('expiry_date', $today)->where('ins',$tenant->id)->get();
            $setting = RecipientSetting::withoutGlobalScopes()->where('type', 'document')->where('ins', $tenant->id)->first();
            foreach($document_trackers as $document)
            {
                $this->notifyUsers($document, $tenant, $setting);
            }
        }
        $this->info('Notifications sent successfully.');
    }

    public function notifyUsers(DocumentManager $document, $tenant, $setting)
    {
        $users = [
            'responsible' => Hrm::withoutGlobalScopes()->find($document->responsible),
            'co_responsible' => Hrm::withoutGlobalScopes()->find($document->co_responsible)
        ];

        foreach ($users as $role => $user) {
            if ($user && $user->meta) {
                $cleanedNumber = preg_replace('/\D/', '', $user->meta->primary_contact);
                $pattern = '/^(07\d{8}|2547\d{8})$/';

                if (preg_match($pattern, $cleanedNumber)) {
                    $this->processNotification($user, $cleanedNumber, $document, $tenant, $setting);
                }
            }
        }
    }

    private function processNotification($user, $phoneNumber, DocumentManager $document, $tenant, $setting)
    {
        $subject = $this->generateMessage($document,$tenant, $user->fullname);

        $costPer160 = 0.6;
        $totalCharacters = strlen($subject);
        $charCount = ceil($totalCharacters / 160);
        $totalCost = $costPer160 * $charCount;

        $data = [
            'subject' => $subject,
            'user_type' => 'employee',
            'delivery_type' => 'now',
            'message_type' => 'bulk',
            'phone_numbers' => $phoneNumber,
            'sent_to_ids' => $user->id,
            'characters' => $charCount,
            'cost' => $costPer160,
            'user_count' => 1,
            'total_cost' => $totalCost,
            'user_id' => $tenant->id,
            'ins' => $tenant->id,
        ];

        if ($setting->sms === 'yes') {
            $sendSms = new SendSms();
            $sendSms->fill($data);
            $sendSms->user_id = $tenant->id; // Manually assign user_id
            $sendSms->ins = $tenant->id;
            $sendSms->save();

            // Send the SMS
            $this->bulk_sms($data['phone_numbers'], $data['subject'], $sendSms, $tenant->id);
        }
    }

    private function generateMessage(DocumentManager $document,$tenant, string $username): string
    {
        $message = "From {$tenant->sms_email_name} : Dear {$username},";
        $message .= " The document '{$document->name}' ";
        $message .= $document->renewal_date == Carbon::today() ? 'is due for renewal today.' : 'expires today.';
        $message .= " Please take necessary action.";

        return $message;
    }

    public function bulk_sms($mobile, $text_message, $send_sms_id, $ins)
    {
        $sms_server = SmsSetting::withoutGlobalScopes()->where('ins', $ins)->first();
        // Prepare the payload
        $payload = [
            'senderID' => $sms_server->sender,
            'message' => $text_message,
            'phones' => $mobile,
        ];


        // Use GuzzleHTTP to send the message
        try {
            $client = new \GuzzleHttp\Client();
            $apiToken = $sms_server->username;

            // Make the async request to the API
            $promise = $client->postAsync('https://api.mobilesasa.com/v1/send/bulk', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $apiToken,
                ],
                'json' => $payload,
            ]);

            // Wait for the response of the async request
            $response = $promise->wait();

            // Handle the successful response
            $body = $response->getBody();
            $content = json_decode($body->getContents(), true); // Convert to array
            // dd($content);

            if (isset($content['bulkId']) && isset($content['status'])) {

                $phone_numbers = explode(',', $mobile);
                $data = [
                    'send_sms_id' => $send_sms_id->id,
                    'message_response_id' => $content['bulkId'],
                    'status' => 1,
                    'message_type' => 'bulk',
                    'phone_number_count' => count($phone_numbers),
                    'response_code' => $content['responseCode'] ?? null,
                    'ins' => $ins,
                    'user_id' => $ins
                ];


                // Save the SMS response
                // dd($data);
                try {
                    $user = auth()->user();
                    if ($user) {
                        $data['user_id'] = $user->id;
                        $data['ins'] = $user->ins;
                    } else {
                        // Handle unauthenticated case
                        $data['user_id'] = $ins;
                        $data['ins'] = $ins;
                    }
                    SmsResponse::withoutGlobalScopes(['ins'])->create($data);
                } catch (\Throwable $th) {
                    //throw $th;
                    \Log::error("An error occurred: " . $th->getMessage());
                }

                // Log the response
                echo "SMS Sent Successfully: " . json_encode($content) . "\n";
            } else {
                echo "Unexpected response format: " . json_encode($content) . "\n";
            }
        } catch (RequestException $e) {
            // Handle request exceptions (e.g., network errors)
            echo "Failed to send SMS: " . $e->getMessage() . "\n";

            if ($e->hasResponse()) {
                $errorResponse = $e->getResponse();
                $errorBody = $errorResponse->getBody()->getContents();
                echo "Error Response: " . $errorBody . "\n";
            }
        } catch (\Exception $e) {
            // Handle any other errors
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }
}
