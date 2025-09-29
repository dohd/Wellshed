<?php

namespace App\Console\Commands;

use App\Models\stakeholder\Stakeholder;
use DateTime;
use Illuminate\Console\Command;

class StakeholderAccessManager extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'manage:stakeholder-access';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage Stakeholder Access';

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

        $stakeholders = Stakeholder::withoutGlobalScopes()->where('is_stakeholder', 1)->get();

        $today = new DateTime();

        foreach ($stakeholders as $sH) {

            $cutoffDate = new DateTime($sH->sh_access_end);

            if ($today >= $cutoffDate) {

                $sH->login_access = 0;
                $sH->save();
            }
        }

    }
}
