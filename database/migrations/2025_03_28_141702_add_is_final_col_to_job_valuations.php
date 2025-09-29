<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsFinalColToJobValuations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('job_valuations', function (Blueprint $table) {
            $table->integer('is_final')->nullable();
            $table->string('completion_cert')->nullable();
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
            $table->dropColumn(['is_final', 'completion_cert']);
        });
    }
}
