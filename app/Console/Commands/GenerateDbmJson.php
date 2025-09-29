<?php

namespace App\Console\Commands;

use App\Models\Company\Company;
use App\Models\dailyBusinessMetric\DailyBusinessMetric;
use App\Repositories\DbmPayloadTrait;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema; // for hasColumn safety
use DateTime;

class GenerateDbmJson extends Command
{
    use DbmPayloadTrait;

    protected $signature = 'json:dbm-generate
                            {--date= : YYYY-MM-DD (default: today Africa/Nairobi)}
                            {--company_id= : Only this company id}
                            {--overwrite : Regenerate even if a JSON already exists}';

    protected $description = 'Generate and save DBM JSON(s) into storage (one per company per date)';

    public function handle()
    {
        $date = $this->option('date') ?: Carbon::now('Africa/Nairobi')->toDateString();

        $query = DailyBusinessMetric::withoutGlobalScopes()
            ->whereDate('date', $date);

        if ($this->option('company_id')) {
            $query->where('ins', $this->option('company_id'));
        }

        $dbms = $query->get();
        if ($dbms->isEmpty()) {
            $this->warn("No DBM rows for {$date}.");
            return 0;
        }

        // group by company to ensure max 1 JSON per company per date
        $byCompany = $dbms->groupBy('ins');

        // NEW: detect once whether the column exists
        $hasReportJsonColumn = Schema::hasColumn('daily_business_metrics', 'report_json'); // table name

        foreach ($byCompany as $companyId => $rows) {
            $company = Company::find($companyId);
            if (!$company) {
                $this->warn("Skip company {$companyId}: not found.");
                continue;
            }

            $dir      = "dbm_reports/{$companyId}/{$date}";
            $filename = "Comprehensive-Operational-Summary-{$date}.json"; // same basename as PDF, but .json
            $path     = "{$dir}/{$filename}";

            // Check DB hint AND storage file to prevent duplicate work
            $alreadyRecorded = $rows->contains(function ($r) use ($filename, $path) {
                return ($r->report_filename === $filename) || ($r->report_storage_path === $path);
            });
            $fileExists = Storage::exists($path);

            if (!$this->option('overwrite') && ($alreadyRecorded && $fileExists)) {
                $this->info("Skip company {$companyId}: JSON already exists at storage/app/{$path}");
                continue;
            }

            // Pick a representative DBM (latest updated)
            $dbm = $rows->sortByDesc(function ($row) {
                return $row->updated_at ?: $row->created_at;
            })->first();

            // Build payload (same as PDF), then map to JSON structure
            $dateToday   = new DateTime($dbm->date);
            $payload     = $this->buildDbmPayload($dbm, $dateToday);
            $jsonPayload = $this->buildDbmJsonPayload($payload, $dateToday);
            $json        = $this->renderDbmJson($jsonPayload); // string

            // Save the file to disk
            Storage::makeDirectory($dir);
            Storage::put($path, $json);

            // Stamp ALL DBM rows for this company/date with the same filename/path/time (+ JSON if column exists)
            $now = now();
            foreach ($rows as $row) {
                $row->report_filename     = $filename;
                $row->report_storage_path = $path; // relative to storage/app
                $row->report_generated_at = $now;

                // NEW: persist JSON too (if column exists)
                if ($hasReportJsonColumn) {
                    $row->report_json = $json; // store raw JSON string
                }

                $row->save();
            }

            $this->info("Saved: storage/app/{$path} (and recorded filename/path on " . count($rows) . " DBM rows)"
                . ($hasReportJsonColumn ? " + report_json persisted" : " (report_json column missing, skipped)"));
        }

        return 0;
    }
}
