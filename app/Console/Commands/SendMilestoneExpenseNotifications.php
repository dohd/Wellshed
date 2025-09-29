<?php

namespace App\Console\Commands;

use App\Models\Company\Company;
use App\Models\Company\RecipientSetting;
use App\Models\Company\SmsSetting;
use App\Models\hrm\Hrm;
use App\Models\Notification\RecipientNotification;
use App\Models\project\Project;
use App\Models\send_email\SendEmail;
use App\Models\send_sms\SendSms;
use App\Models\sms_response\SmsResponse;
use App\Repositories\Focus\general\RosemailerRepository;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Console\Command;

class SendMilestoneExpenseNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:milestone_expense_notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sending Milestone Expense Notification';

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
        try {
            $projects = Project::withoutGlobalScopes()->whereHas('misc', fn($q) => $q->withoutGlobalScopes()->where('name','!=','Completed'))->get();
            foreach ($projects as $project) {
                $milestones = $project->milestones()->withoutGlobalScopes()->get();
                $company = Company::find($project->ins);
                $setting = RecipientSetting::withoutGlobalScopes()->where('ins', $project->ins)->where(['type' => 'milestone_expense'])->first();
                if(count($milestones) > 0) {
                    foreach ($milestones as $milestone) {
                        if($milestone->amount == 0) continue;
                        $expense_amount = $milestone->amount - $milestone->balance;
                        $expense_percent = ($expense_amount/$milestone->amount) * 100;
                       
                        $milestone_completion_percent = $milestone->milestone_completion;
                        if($milestone_completion_percent == 0) continue;
                        $previous_notification_balance = RecipientNotification::where('milestone_id', $milestone->id)->where(['setting_type'=>'milestone_expense', 'recipient_setting_id'=>$setting->id])->first();
                        if($expense_percent - $milestone_completion_percent > $setting->target)
                        {
                            if(empty($previous_notification_balance)){
                                $milestone_users = $milestone->users()->withoutGlobalScopes()->get()->toArray();
                               
                                $milestone_users_ids = array_map('strval', array_column($milestone_users, 'id'));
                                // Get unique values
                                $phone_numbers = [];
                                $user_ids = [];
                                $emails = [];
                                $pattern = '/^(07\d{8}|2547\d{8})$/';
                                foreach($milestone_users_ids as $recipient_id){
                                    $user = Hrm::withoutGlobalScopes()->where('status',1)->find($recipient_id);
                                    if($user->meta){
                                        $cleanedNumber = preg_replace('/\D/', '', $user->meta->secondary_contact);
                                        if (preg_match($pattern, $cleanedNumber)) {
                                            $phone_numbers[] = $cleanedNumber;
                                            $user_ids[] = $user->id;
                                            $emails[] = $user->personal_email;
                                        }
                                    }
                                }
                                $contacts = implode(',', $phone_numbers);
                                $users = implode(',', $user_ids);
                                $project_no = gen4tid("PRJ-",$project->tid);
                                $customer = $project->customer()->withoutGlobalScopes()->first();
                                $customer_name = $customer->company ?: $customer->name;
                                $branch = $project->branch()->withoutGlobalScopes()->first();
                                $branch_name = $branch ? $branch->name : 'Head Office';
                                // dd($branch);
                                // $subject = "From {$company->sms_email_name}: Please be informed that the gross profit margin for budgeted amount vs expensed amount for {$project_no}, related to {$customer_name}, at {$branch_name} has fallen to less than zero. Kindly review.";
                                $cost_per_160 = 0.6;
                                $subject = "From {$company->sms_email_name}: This is to inform you that for the milestone {$milestone->name} in the project {$project_no}, there is a significant discrepancy in progress: \n
                                        The percentage of expensed amount exceeds the percentage of work completed by more than {$setting->target}%. Kindly review.";
                                $totalCharacters = strlen($subject);
                                $charCount = ceil($totalCharacters/160);
                                $count_users = count($user_ids);
                                $data = [
                                    'subject' => $subject,
                                    'user_type' =>'employee',
                                    'delivery_type' =>'now',
                                    'message_type' => 'bulk',
                                    'phone_numbers' => $contacts,
                                    'sent_to_ids' => $users,
                                    'characters' => $charCount,
                                    'cost' => $cost_per_160,
                                    'user_count' => $count_users,
                                    'total_cost' => $cost_per_160*$charCount*$count_users,
                                    'user_id' => $project->user_id,
                                    'ins' => $project->ins,
    
                                ];
                                if($setting->sms == 'yes'){
                                    $send_sms = new SendSms();
                                    $send_sms->fill($data);
                                    $send_sms->user_id = $project->user_id;  // Manually assign user_id
                                    $send_sms->ins = $project->ins;
                                    $send_sms->save();
                                    $this->bulk_sms($data['phone_numbers'], $data['subject'], $send_sms, $company->id);
                                }
                                if($setting->email == 'yes'){
                                    $mail_to = array_shift($emails);
                                    $others = $emails;
                                    //Send EMAILs
                                    $email_input = [
                                        'text' => $subject,
                                        'subject' => 'Urgent: Milestone Progress Alert',
                                        'email' => $others,
                                        'mail_to' => $mail_to
                                    ];
                                    $email = (new RosemailerRepository($project->ins))->send_group($email_input['text'], $email_input);
                                    $email_output = json_decode($email);
                                    if ($email_output->status === "Success"){

                                        $email_data = [
                                            'text_email' => $email_input['text'],
                                            'subject' => $email_input['subject'],
                                            'user_emails' => $email_input['mail_to'],
                                            'user_ids' => $users,
                                            'ins' => $project->ins,
                                            'user_id' => $project->user_id,
                                            'status' => 'sent'
                                        ];
                                        SendEmail::create($email_data);
                                    }
                                }
                                
                                //Create a new notification RecipientNotification(model)
                                $data = [
                                    'recipient_setting_id' => $setting->id,
                                    'milestone_id' => $milestone->id,
                                    'setting_type' => 'milestone_expense',
                                ];
                                if($setting->sms == 'yes' || $setting->email == 'yes') RecipientNotification::create($data);
                            }
                        }

                    }
                }
            }
            $this->info(now() .' Sending Milestone Expense Notification: ');
        } catch (\Throwable $th) {
            //throw $th;
            $this->error(now() .' '. $th->getMessage() . ' at ' . $th->getFile() . ':' . $th->getLine());
        }
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
