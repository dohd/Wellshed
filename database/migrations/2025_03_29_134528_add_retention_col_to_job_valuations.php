<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRetentionColToJobValuations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('job_valuations', function (Blueprint $table) {
            $table->decimal('perc_retention', 10, 4)->default(0);
            $table->decimal('retention', 16, 4)->default(0);
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
            $table->dropColumn(['perc_retention', 'retention']);
        });
    }
}
