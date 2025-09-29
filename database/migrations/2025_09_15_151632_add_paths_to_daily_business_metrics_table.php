<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPathsToDailyBusinessMetricsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('daily_business_metrics', function (Blueprint $table) {
            $table->string('report_filename')->nullable();
            $table->string('report_storage_path')->nullable(); // storage/app/...
            $table->timestamp('report_generated_at')->nullable();
            $table->string('report_email_to')->nullable();
            $table->timestamp('report_emailed_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('daily_business_metrics', function (Blueprint $table) {
            $table->dropColumn(['report_filename', 'report_storage_path', 'report_generated_at','report_email_to','report_emailed_at']);
        });
    }
}
