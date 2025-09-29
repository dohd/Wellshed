<?php

namespace App\Console\Commands;

use App\Models\Access\User\User;
use App\Models\account\Account;
use App\Models\bank\Bank;
use App\Models\Company\Company;
use App\Models\Company\EmailSetting;
use App\Models\Company\SmsSetting;
use App\Models\currency\Currency;
use App\Models\customer\Customer;
use App\Models\invoice\Invoice;
use App\Models\items\InvoiceItem;
use App\Models\send_email\SendEmail;
use App\Models\send_sms\SendSms;
use App\Models\sms_response\SmsResponse;
use App\Models\term\Term;
use App\Repositories\Focus\general\RosemailerRepository;
use App\Repositories\Focus\invoice\InvoiceRepository;
use Auth;
use DB;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Console\Command;

class CreatePMEInvoice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pme-invoice:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create PME Software Service Invoice';

    /**
     * Invoice Accounting Repository
     */
    protected $invoiceRepository;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(InvoiceRepository $invoiceRepository)
    {
        parent::__construct();
        $this->invoiceRepository = $invoiceRepository;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            // mimic auth
            $ins = Company::where('is_main', 1)->first(['id'])->id;
            $main_tenant = Company::where('is_main', 1)->first();
            $user = User::make(['id' => null, 'ins' => $ins]);
            Auth::login($user);

            $bank = Bank::withoutGlobalScopes()->where(['ins' => $ins, 'enable' => 'yes'])->first();
            $term = Term::withoutGlobalScopes()->where('ins', $ins)->where('title', 'LIKE', '%No Terms%')->first();
            $currency = Currency::withoutGlobalScopes()->where(['ins' => $ins, 'code' => 'KES'])->first();
            $incomeAccount = Account::withoutGlobalScopes()->where('ins', $ins)
                ->where('holder', 'LIKE', '%Project Management%')
                ->whereHas('account_type_detail', fn($q) => $q->where('system_rel', 'income'))
                ->first();

            $customers = Customer::withoutGlobalScopes()
                ->where('ins', $ins)
                ->whereHas('tenant', function($q) {
                    $q->where('companies.status', 'active');
                    $q->whereDate('billing_date', date('Y-m-d'));
                })
                ->with(['tenant_package', 'tenant'])
                ->get();
            $email_setting = EmailSetting::withoutGlobalScopes()->where('ins',$main_tenant->id)->first();
            $this->info(now() .' Customer Count: '. $customers->count());

            $failedCustomers = [];
            $successIds = [];
            foreach ($customers as $customer) {
                // break;
                try {
                    $result = DB::transaction(function() use($bank, $term, $currency, $incomeAccount, $customer, $main_tenant, $email_setting) {
                        $tenantPackage = $customer->tenant_package;
                        $invoice = Invoice::create([
                            'tid' => Invoice::max('tid')+1,
                            'customer_id' => $customer->id,
                            'invoicedate' => $customer->tenant->billing_date,
                            'invoiceduedate' => $customer->tenant->billing_date,
                            'tax_id' => $tenantPackage->vat_rate,
                            'bank_id' => $bank->id,
                            'validity' => 0,
                            'account_id' => $incomeAccount->id,
                            'currency_id' => $currency->id,
                            'fx_curr_rate' => $currency->rate,
                            'term_id' => $term->id,
                            'notes' => 'Project Management ERP Subscription Fee',
                            'status' => 'due',
                            'taxable' => $tenantPackage->net_cost,
                            'subtotal' => $tenantPackage->net_cost,
                            'tax' => $tenantPackage->vat,
                            'total' => $tenantPackage->total_cost,
                        ]);
                        InvoiceItem::create([
                            'invoice_id' => $invoice->id,
                            'numbering' => 1,
                            'description' => $invoice['notes'],
                            'product_qty' => 1,
                            'product_price' => $invoice['taxable'],
                            'product_tax' => $invoice['tax'],
                            'product_subtotal' => $invoice['subtotal'],
                            'tax_rate' => $invoice['tax_id'],
                            'product_amount' => $invoice['total'],
                            'unit' => 'ITEM',
                        ]);
                        // accounting
                        $this->invoiceRepository->post_invoice($invoice);

                        //Send Sms and 
                        $customerName = $customer->company ?? $customer->name;
                        $emailInput = [
                            'subject' => "Invoice #{{ $invoice->tid }} from {$main_tenant->cname}",
                            'mail_to' => $customer->email,
                            'name' => $customerName,
                        ];
                        $smsData = [
                            'user_type' => 'customer',
                            'delivery_type' => 'now',
                            'message_type' => 'single',
                            'phone_numbers' => $customer->phone,
                            'sent_to_ids' => $customer->id,

                        ];
                        $secureToken = hash('sha256', $invoice->id . env('APP_KEY'));
                        $link = route('invoice_print', [
                            'invoice_id' => $invoice->id,
                            'token' => $secureToken
                        ]);
                    
                        // Handle each status case
                        if ($invoice) {
                            // For 
                            $message = "From ".$main_tenant->cname . ": Dear $customerName, We hope this message finds you well,\n\n";
                            $message .= "Your invoice is ready. Please view it using the link below:\n\n";
                            $message .= "{$link}\n\n";
                            $message .= "Thank you for choosing us!\n\n";
                            $message .= "Best regards,\n";
                            $smsText = $message;
                            $emailInput['text'] = "
                                <p>Dear $customerName,</p>
                                <p>We hope this message finds you well. Please find your invoice attached for your recent transaction with us.</p>
                                <p><strong>Invoice Details:</strong></p>
                                <ul>
                                    <li>Invoice Number: {$invoice->tid}</li>
                                    <li>Invoice Date: {$invoice->invoicedate}</li>
                                    <li>Total Amount: " . number_format($invoice->total, 2) . "</li>
                                </ul>
                                <p><strong>Bank Payment Instructions (For Future Payments):</strong></p>
                                <p>You can make payments using the following details:</p>
                                <ul>
                                    <li>Paybill: 522522</li>
                                    <li>Account Number: 1295110113</li>
                                </ul>
                                <p>Thank you for choosing {$main_tenant->cname}. If you have any questions regarding this invoice or need assistance, feel free to reach out to us at {$email_setting->customer_statement_email_to} or call {$email_setting->office_number}.</p>
                                <p>We appreciate your prompt attention to this matter.</p>
                                <p>Best regards,</p>
                                <p>{$main_tenant->cname}.</p>
                                <p>{$link}</p>";

                        } 
                    
                        // Only proceed 
                        if (isset($smsText)) {
                            // Prepare SMS data
                            $smsData['subject'] = $smsText;
                            $cost_per_160 = 0.6;
                            $charCount = strlen($smsText);
                            $blocks = ceil($charCount / 160);
                            $smsData['characters'] = $charCount;
                            $smsData['cost'] = $cost_per_160;
                            $smsData['user_count'] = 1;
                            $smsData['total_cost'] = $cost_per_160*$blocks;
                            $send_sms = new SendSms();
                            $send_sms->fill($smsData);
                            $send_sms->user_id = $main_tenant->id;  // Manually assign user_id
                            $send_sms->ins = $main_tenant->id;
                            $send_sms->save();
                    
                            $this->bulk_sms($smsData['phone_numbers'], $smsData['subject'], $send_sms, $main_tenant->id);
                            $email = (new RosemailerRepository($main_tenant->id))->send($emailInput['text'], $emailInput);
                            $email_output = json_decode($email);
                            if ($email_output->status === "Success"){
        
                                $email_data = [
                                    'text_email' => $emailInput['text'],
                                    'subject' => $emailInput['subject'],
                                    'user_emails' => $emailInput['mail_to'],
                                    'user_ids' => $customer->id,
                                    'ins' => $main_tenant->id,
                                    'user_id' => $main_tenant->id,
                                    'status' => 'sent'
                                ];
                                SendEmail::create($email_data);
                            }
                            
                        }

                        return $invoice;
                    });

                    $successIds[] = $result->id;
                } catch (\Throwable $th) {
                    $failedCustomers[] = $customer->id; 
                    $this->error(now() .' '. $th->getMessage() . ' at ' . $th->getFile() . ':' . $th->getLine());
                }
            }        
            $this->info(now() .' Successful Invoice IDs: '. implode(',', $successIds));
            if ($failedCustomers) $this->error(now() . ' Failed Transaction Customer IDs: ' . implode(',', $failedCustomers));
        } catch (\Throwable $th) {
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
