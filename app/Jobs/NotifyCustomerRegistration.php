<?php

namespace App\Jobs;

use App\Models\Company\Company;
use App\Models\send_email\SendEmail;
use App\Repositories\Focus\general\RosemailerRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NotifyCustomerRegistration implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $customer;
    protected $password;
    protected $ins;

    public function __construct($customer, $password, $ins)
    {
        $this->customer  = $customer;
        $this->password  = $password;
        $this->ins       = $ins;
    }

    public function handle()
    {
        try {
            $customer = $this->customer;
            $company  = Company::where('is_main',1)->first();

            if (!$customer || !$company) {
                Log::warning("NotifyCustomerRegistration: Missing company or customer");
                return;
            }

            // ✅ Send welcome email to customer
            $this->emailCustomer($customer, $company);

            // ✅ Send notification to company
            if (!empty($company->email)) {
                $this->emailCompanyNotification($customer, $company);
            }

        } catch (\Throwable $th) {
            Log::error("NotifyCustomerRegistration Job Error: " . $th->getMessage());
        }
    }

    /**
     * Send welcome email to customer
     */
    protected function emailCustomer($customer, $company)
    {
        $body = "
            <p>Dear {$customer->username},</p>

            <p>Welcome to {$company->cname}! Your account has been successfully created.</p>

            <p>You can now access your customer portal using the login details below:</p>

            <p>
                <strong>Email:</strong> {$customer->email}<br>
                <strong>Password:</strong> {$this->password}
            </p>

            <p>
                For security, we recommend changing your password after your first login.
            </p>

            <p>
                Thank you for choosing {$company->cname}.
            </p>

            <p>Best regards,<br>{$company->cname}</p>
        ";

        $this->sendEmail([
            'text'          => $body,
            'subject'       => 'Your Account Login Details',
            'mail_to'       => $customer->email,
            'customer_name' => $customer->name,
        ], $customer);
    }

    /**
     * Notify company about new customer registration
     */
    protected function emailCompanyNotification($customer, $company)
    {
        $phone = $customer->phone ?: '';

        $body = "
            <p>Dear {$company->cname},</p>

            <p>A new customer has successfully registered on the system.</p>

            <p><strong>Customer Details:</strong></p>
            <p>
                Name: {$customer->first_name}<br>
                Email: {$customer->email}" .
                ($phone ? "<br>Phone: {$phone}" : "") . "
            </p>

            <p>Please ensure the customer is properly onboarded and assisted as needed.</p>

            <p>Best regards,<br>{$company->cname}</p>
        ";

        $this->sendEmail([
            'text'          => $body,
            'subject'       => 'New Customer Registration Notification',
            'mail_to'       => $company->email,
            'customer_name' => $company->cname,
        ], $company);
    }

    /**
     * Generalized email handler
     */
    protected function sendEmail(array $emailInput, $user)
    {
        try {

            $repo  = new RosemailerRepository($this->ins);
            $email = $repo->send($emailInput['text'], $emailInput);
            $response = json_decode($email);

            if (!empty($response) && $response->status === "Success") {
                SendEmail::create([
                    'text_email'   => $emailInput['text'],
                    'subject'      => $emailInput['subject'],
                    'user_emails'  => $emailInput['mail_to'],
                    'user_ids'     => $user->id ?? null,
                    'user_type'    => 'employee',
                    'delivery_type'=> 'now',
                    'status'       => 'sent'
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Email sending failed: ' . $e->getMessage());
        }
    }
}
