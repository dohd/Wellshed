<?php

namespace App\Console\Commands;

use App\Models\Company\Company;
use App\Models\dailyBusinessMetric\DailyBusinessMetric;
use App\Models\send_email\SendEmail;
use App\Repositories\Focus\general\RosemailerRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SendDailyJSON extends Command
{
    protected $signature = 'json:dbm-email
                            {--date= : YYYY-MM-DD (default: today Africa/Nairobi)}
                            {--company_id= : Only this company id}
                            {--to= : Override recipient email (single address)}
                            {--resend : Send even if already emailed}
                            {--dry-run : Don\'t send, just show what would happen}';

    protected $description = 'Email the already-generated DBM JSON reports (one email per company per date), embedding JSON content in the email body.';

    public function handle()
    {
        $date = $this->option('date') ?: Carbon::now('Africa/Nairobi')->toDateString();

        $query = DailyBusinessMetric::withoutGlobalScopes()->whereDate('date', $date);
        if ($this->option('company_id')) {
            $query->where('ins', (int) $this->option('company_id'));
        }

        $dbms = $query->get();
        if ($dbms->isEmpty()) {
            $this->warn("No DBM rows for {$date}.");
            return 0;
        }

        // group by company so we only send once per company/date
        $byCompany = $dbms->groupBy('ins');

        foreach ($byCompany as $companyId => $rows) {
            $company = Company::find($companyId);
            if (!$company) {
                $this->warn("Skip company {$companyId}: not found.");
                continue;
            }

            // Prefer recorded filename/path then fall back to canonical JSON path
            $firstRow = $rows->sortByDesc(function ($row) {
                return $row->updated_at ?: $row->created_at;
            })->first();

            $dir      = "dbm_reports/{$companyId}/{$date}";
            $filename = $firstRow->report_filename ?: "Comprehensive-Operational-Summary-{$date}.json";
            // If report_filename still points to a .pdf from earlier runs, coerce to .json
            if (Str::endsWith(strtolower($filename), '.pdf')) {
                $filename = preg_replace('/\.pdf$/i', '.json', $filename);
            }

            $path = $firstRow->report_storage_path ?: "{$dir}/{$filename}";
            if (Str::endsWith(strtolower($path), '.pdf')) {
                $path = preg_replace('/\.pdf$/i', '.json', $path);
            }

            if (!Storage::exists($path)) {
                $this->warn("Missing JSON for company {$companyId}: storage/app/{$path} not found. Skipping.");
                continue;
            }

            // Resolve recipient
            $to = $this->option('to')
                ?: ($company->chatgpt_email ?? null);

            if (!$to) {
                $this->warn("No recipient email for company {$companyId}. Set --to= or add a field on Company. Skipping.");
                continue;
            }

            // De-dupe unless --resend
            $alreadyEmailed = $rows->contains(function ($r) use ($to) {
                return !is_null($r->report_emailed_at) && (!empty($r->report_email_to) ? $r->report_email_to === $to : true);
            });

            if ($alreadyEmailed && !$this->option('resend')) {
                $this->info("Skip company {$companyId}: already emailed to {$to} for {$date}. Use --resend to force.");
                continue;
            }

            // Compose subject & body (embed JSON)
            $subjectName = $company->sms_email_name ?? $company->cname ?? 'Operations';
            $subject     = "The 8pm Daily Operations Summary Report - JSON Version";
            $jsonBody    = Storage::get($path);

            // Optional link to the public/controller JSON (if you keep it)
            $link = route('daily_report_json', ['uuid' => $firstRow->dbm_uuid]);
            if (($decoded = json_decode($jsonBody, true)) !== null) {
                $jsonBody = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }

            $text = $jsonBody;

            if ($this->option('dry-run')) {
                $this->line("[DRY RUN] Would email {$to} with JSON body from storage/app/{$path}");
                continue;
            }

            // Send email (no attachment; JSON is in the body)
            $email_input = [
                'text'      => $text,
                'subject'   => $subject,
                'mail_to'   => $to,
                // 'statement' => null, // not needed; we are NOT attaching
                'name'      => pathinfo($filename, PATHINFO_FILENAME),
            ];

            try {
                $email_sent   = (new RosemailerRepository($company->id))->send($email_input['text'], $email_input);
                $email_output = json_decode($email_sent);

                if ($email_output && ($email_output->status ?? null) === "Success") {
                    // Update ALL rows for this company/date to stamp the email delivery
                    $now = now();
                    foreach ($rows as $row) {
                        $row->report_email_to   = $to;
                        $row->report_emailed_at = $now;
                        // backfill filename/path if missing or still .pdf
                        if (empty($row->report_filename) || Str::endsWith(strtolower($row->report_filename), '.pdf')) {
                            $row->report_filename = $filename;
                        }
                        if (empty($row->report_storage_path) || Str::endsWith(strtolower($row->report_storage_path), '.pdf')) {
                            $row->report_storage_path = $path;
                        }

                        $row->save();
                    }

                    // Optional: log in SendEmail model
                    SendEmail::create([
                        'text_email'  => $subject,
                        'subject'     => $subject,
                        'user_emails' => $to,
                        'user_ids'    => $company->id,
                        'ins'         => $company->id,
                        'user_id'     => $company->id,
                        'status'      => 'sent',
                    ]);

                    $this->info("Emailed {$to} with JSON body from storage/app/{$path}");
                } else {
                    $msg = is_object($email_output) ? (($email_output->message ?? 'unknown failure')) : 'unknown failure';
                    $this->error("Failed emailing company {$companyId} ({$to}): {$msg}");
                }
            } catch (\Exception $ex) {
                $this->error("Exception emailing company {$companyId} ({$to}): {$ex->getMessage()}");
            }
        }

        return 0;
    }
}
