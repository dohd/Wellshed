<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCasualRemunColToJobValuationExpsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('job_valuation_exps', function (Blueprint $table) {
            $table->string('casual_remun_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('job_valuation_exps', function (Blueprint $table) {
            $table->dropColumn(['casual_remun_id']);
        });
    }
}
