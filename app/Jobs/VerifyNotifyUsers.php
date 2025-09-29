<?php

namespace App\Jobs;

use App\Models\send_email\SendEmail;
use App\Repositories\Focus\general\RosemailerRepository;
use DB;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class VerifyNotifyUsers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $ins;
    protected $user;
    protected $message;
    protected $subject;

    public function __construct($ins, $user, $message, $subject)
    {
        $this->ins = $ins;
        $this->user = $user;
        $this->message = $message;
        $this->subject = $subject;
    }

    public function handle(): void
    {
        try {
            // Assumes 'ins' is on the user model
            $ins = $this->ins;

            // === SEND EMAIL ===
            if ($this->user['email']) {
                DB::beginTransaction();

                $email_input = [
                    'text' => $this->message,
                    'subject' => $this->subject,
                    'mail_to' => $this->user['email'],
                ];

                $response = (new RosemailerRepository($ins))->send($email_input['text'], $email_input);
                $email_output = json_decode($response);

                if ($email_output->status === "Success") {
                    SendEmail::create([
                        'text_email' => $email_input['text'],
                        'subject' => $email_input['subject'],
                        'user_emails' => $email_input['mail_to'],
                        'ins' => $ins,
                        'user_id' => $this->user['id'],
                        'status' => 'sent'
                    ]);
                }

                DB::commit();
            }
        } catch (Exception $ex) {
            DB::rollBack();
            \Log::error('VerifyNotifyUsers Job Error', [
                'message' => $ex->getMessage(),
                'code' => $ex->getCode(),
                'file' => $ex->getFile(),
                'line' => $ex->getLine(),
            ]);
        }
    }
}
