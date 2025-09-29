<?php

namespace App\Console\Commands;

use App\Models\casual_labourer_remuneration\CasualLabourersRemuneration;
use App\Models\Company\Company;
use App\Models\Company\RecipientSetting;
use App\Models\Company\SmsSetting;
use App\Models\hrm\Hrm;
use App\Models\Notification\RecipientNotification;
use App\Models\project\Project;
use App\Models\quote\Quote;
use App\Models\send_email\SendEmail;
use App\Models\send_sms\SendSms;
use App\Models\sms_response\SmsResponse;
use App\Repositories\Focus\general\RosemailerRepository;
use DB;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Console\Command;

class SendSmsEmailNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notify:sms_and_email_notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Sms and Email Notifications to Users';

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
            DB::beginTransaction();
            $projects = Project::withoutGlobalScopes()->whereHas('misc', fn($q) => $q->withoutGlobalScopes()->where('name','!=','Completed'))->get();
            foreach ($projects as $project) {
                    $total_actual = 0;
                    $total_estimate = 0;
                    $total_balance = 0;
                    $expenseTotalBudget = 0;
                    $total_actual_balance = 0;
                    $quotes = $project->quotes()->withoutGlobalScopes()->get();
                    foreach ($quotes as $quote) {
                        $siQuote = Quote::withoutGlobalScopes()->where('id', $quote->id)
                        ->with(['stockIssues' => fn($q) => $q->withoutGlobalScopes()])
                        ->get()->toArray();//->pluck('stock_issues');
                        $stock_issues_arrays = array_column($siQuote, 'stock_issues');
                        // Flatten the array of arrays into a single array
                        $stock_issues = array_merge(...$stock_issues_arrays);

                        $stockIssuesValue = array_reduce($stock_issues, function($carry, $stock_issue) {
                            return $carry + $stock_issue['total'];
                        }, 0);


                        $actual_amount = $quote->subtotal;

                        $budgetedAmount = 0;
                        $budget = $quote->budget()->withoutGlobalScopes()->first();
                        if ($budget) $budgetedAmount = $budget->items()->withoutGlobalScopes()->sum(DB::raw('round(new_qty*price)'));

                        $dir_purchase_amount = $project->purchase_items()->withoutGlobalScopes()->sum('amount') / $quotes->count();
                        $proj_grn_amount = $project->grn_items()->withoutGlobalScopes()->sum(DB::raw('round(rate*qty)')) / $quotes->count();
                        $labour_amount = $project->labour_allocations()->withoutGlobalScopes()->sum(DB::raw('hrs * 500')) / $quotes->count();

                        $quotesCount = $quotes->count();

                            // Avoid division by zero
                            if ($quotesCount === 0) {
                                $casuals_remunerations_amount = 0;
                            } else {
                                $casuals_remunerations_amount = CasualLabourersRemuneration::withoutGlobalScopes()
                                    ->whereHas('labourAllocations', function($query) use ($project) {
                                        $query->where('project_id', $project->id)->withoutGlobalScopes();
                                    })
                                    ->sum(DB::raw('total_amount')) / $quotesCount;
                            }

                        $expense_amount = $dir_purchase_amount + $proj_grn_amount + $labour_amount + $casuals_remunerations_amount + $stockIssuesValue;
                        $project_stock = $quote->projectstock()->withoutGlobalScopes()->get();
                        if (count($project_stock) > 0) $expense_amount += $project_stock->sum('total');

                        $balance = $budgetedAmount - $expense_amount;
                        $actual_balance = $actual_amount - $expense_amount;
                        // aggregate
                        $total_actual += $actual_amount;
                        $total_estimate += $expense_amount;
                        $total_balance += $balance;
                        $expenseTotalBudget += $budgetedAmount;
                        $total_actual_balance += $actual_balance;
                    }
                    $setting = RecipientSetting::withoutGlobalScopes()->where('ins', $project->ins)->where(['type' => 'project_percentage','uom' => '%'])->first();
                    $balance_setting = RecipientSetting::withoutGlobalScopes()->where('ins', $project->ins)->where(['type' => 'project_amount','uom' => 'AMOUNT'])->first();
                    $gross_profit_percent = round(div_num($total_actual_balance, $total_actual) * 100);
                    $company = Company::find($project->ins);
                    // printlog($total_estimate, $total_balance, $expenseTotalBudget,$total_actual, round(div_num($total_balance, $expenseTotalBudget) * 100));
                    if($balance_setting){
                        if($total_balance < $balance_setting->target &&  $project->id > $balance_setting->latest_project_id){
                            //  dd(RecipientNotification::where('reference_id', $project->id)->where(['setting_type'=>'project', 'recipient_setting_id'=>$balance_setting->id])->first());
                            $previous_notification_balance = RecipientNotification::where('reference_id', $project->id)->where(['setting_type'=>'project_amount', 'recipient_setting_id'=>$balance_setting->id])->first();
                            if(!$previous_notification_balance){
                                //Send a notification (SMS and EMAIL) for Number 3
                                $project_employees = $project->users()->withoutGlobalScopes()->get()->toArray();
                                $recipients_ids = explode(',',$balance_setting->recipients);
                                $project_users_ids = array_map('strval', array_column($project_employees, 'id'));
                                $combined_ids = array_merge($recipients_ids, $project_users_ids);
                                // Get unique values
                                $unique_ids = array_unique($combined_ids);
                                $phone_numbers = [];
                                $user_ids = [];
                                $emails = [];
                                $pattern = '/^(07\d{8}|2547\d{8})$/';
                                foreach($unique_ids as $recipient_id){
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
                                $subject = "From {$company->sms_email_name}: Please be informed that the gross profit margin for budgeted amount vs expensed amount for {$project_no}, related to {$customer_name}, at {$branch_name} has fallen to less than zero. Kindly review.";
                                $cost_per_160 = 0.6;
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
                                if($balance_setting->sms == 'yes'){
                                    $send_sms = new SendSms();
                                    $send_sms->fill($data);
                                    $send_sms->user_id = $project->user_id;  // Manually assign user_id
                                    $send_sms->ins = $project->ins;
                                    $send_sms->save();
                                    $this->bulk_sms($data['phone_numbers'], $data['subject'], $send_sms, $company->id);
                                }
                                if($balance_setting->email == 'yes'){
                                    $mail_to = array_shift($emails);
                                    $others = $emails;
                                    //Send EMAILs
                                    $email_input = [
                                        'text' => $subject,
                                        'subject' => 'Project Management Services (WIP)',
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
                                    'recipient_setting_id' => $balance_setting->id,
                                    'reference_id' => $project->id,
                                    'setting_type' => 'project_amount',
                                ];
                                if($balance_setting->sms == 'yes' || $balance_setting->email == 'yes') RecipientNotification::create($data);
                            }
                        }
                    }
                    if($setting){
                        if($setting->target > $gross_profit_percent &&  $project->id > $setting->latest_project_id){
                            $previous_notification = RecipientNotification::where('reference_id', $project->id)->where(['setting_type'=>'project_percentage', 'recipient_setting_id'=>$setting->id])->first();
                            if(!$previous_notification){
                                //send notification
                                $project_employees = $project->users()->withoutGlobalScopes()->get()->toArray();
                                $recipients_ids = explode(',',$setting->recipients);
                                $project_users_ids = array_map('strval', array_column($project_employees, 'id'));
                                $combined_ids = array_merge($recipients_ids, $project_users_ids);
                                // Get unique values
                                $unique_ids = array_unique($combined_ids);
                                $phone_numbers = [];
                                $user_ids = [];
                                $emails = [];
                                $pattern = '/^(07\d{8}|2547\d{8})$/';
                                foreach($unique_ids as $recipient_id){
                                    $user = Hrm::withoutGlobalScopes()->where('status',1)->find($recipient_id);
                                    if($user->meta){
                                        $cleanedNumber = preg_replace('/\D/', '', $user->meta->primary_contact);
                                        if (preg_match($pattern, $cleanedNumber)) {
                                            $phone_numbers[] = $cleanedNumber;
                                            $user_ids[] = $user->id;
                                            $emails[] = $user->email;
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
                                $subject = "From {$company->sms_email_name}: Please be informed that the % gross profit margin for Quoted amount vs expensed amount for {$project_no}, related to {$customer_name}, at {$branch_name} has fallen to {$setting->target}% or below.
                                Kindly review and advise on the necessary actions moving forward.";
                                $cost_per_160 = 0.6;
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
                                        'subject' => 'Project Management Services (WIP)',
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
                                    'reference_id' => $project->id,
                                    'setting_type' => 'project_percentage',
                                ];
                                if($setting->sms == 'yes' || $setting->email == 'yes') RecipientNotification::create($data);
                            }
                        }
                    }
                
            }
            DB::commit();            
            $this->info(now() .' Sms & Email Sent: ');
            
        } catch (\Throwable $th) {
            DB::rollBack();
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
