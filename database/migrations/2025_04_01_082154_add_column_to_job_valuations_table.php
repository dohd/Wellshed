<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToJobValuationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('job_valuations', function (Blueprint $table) {
            $table->date('completion_date')->nullable();
            $table->decimal('dlp_period', 8,2)->nullable();
            $table->decimal('dlp_reminder', 8,2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('job_valuations', function (Blueprint $table) {
            $table->dropColumn(['completion_date','dlp_period','dlp_reminder']);
        });
    }
}
