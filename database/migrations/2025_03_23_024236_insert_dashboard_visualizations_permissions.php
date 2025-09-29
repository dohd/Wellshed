<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class InsertDashboardVisualizationsPermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {

        DB::table('permissions')->insert([
            [
                'name' => 'dashboard-visualizations-recent-ai-leads',
                'display_name' => 'Dashboard-Visualization | Recent A.I Leads Permission',
                'module_id' => 19,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'dashboard-visualizations-recent-ai-transcripts',
                'display_name' => 'Dashboard-Visualization | Recent A.I Chat Transcripts Permission',
                'module_id' => 19,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'dashboard-visualizations-recent-leads',
                'display_name' => 'Dashboard-Visualization | Recent Leads Permission',
                'module_id' => 19,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'dashboard-visualizations-quotes',
                'display_name' => 'Dashboard-Visualization | Recent Quotes Permission',
                'module_id' => 19,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        DB::table('permissions')->whereIn('name', [
            'dashboard-visualizations-recent-ai-leads',
            'dashboard-visualizations-recent-ai-transcripts',
            'dashboard-visualizations-recent-leads',
            'dashboard-visualizations-quotes',
        ])->delete();
    }}
