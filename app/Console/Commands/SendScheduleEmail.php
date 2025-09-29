<?php

namespace App\Console\Commands;

use App\Models\Company\Company;
use App\Models\send_email\SendEmail;
use App\Repositories\Focus\general\RosemailerRepository;
use Illuminate\Console\Command;

class SendScheduleEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:scheduled-email';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sending Scheduled Emails';

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
        $email_list = SendEmail::withoutGlobalScopes()->where('delivery_type', 'schedule')
        ->where('scheduled_date', '<=', now())
        ->where('status','not_sent')
        ->get();
        // dd($email_list, now());
        foreach ($email_list as $list)
        {
            $emails = explode(',', $list->user_emails);
            // dd($emails);
            $mail_to = array_shift($emails);
            $others = $emails;
            $email_input = [
                'text' => $list->text_email,
                'subject' => $list->subject,
                'email' => $others,
                'mail_to' => $mail_to,
            ];
            $company = Company::find($list->ins);
            $mail = (new RosemailerRepository($company->id))->send_group($email_input['text'], $email_input);
            // dd($mail, $company->id);
            $email_output = json_decode($mail);
            if ($email_output->status === "Success"){
                $list->status = 'sent';
                $list->update();
            }
        }
        $this->info(now() .' Scheduled Email Sent: ');
    }
}
