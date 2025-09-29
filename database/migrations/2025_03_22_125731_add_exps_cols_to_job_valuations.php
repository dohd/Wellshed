<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExpsColsToJobValuations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('job_valuations', function (Blueprint $table) {
            $table->decimal('exp_total', 16, 4)->default(0);
            $table->decimal('exp_valuated', 16, 4)->default(0);
            $table->decimal('exp_balance', 16, 4)->default(0);
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
            $table->dropColumn(['exp_total', 'exp_valuated', 'exp_balance']);
        });
    }
}
