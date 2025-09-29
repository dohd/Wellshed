<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOriginIdToJobValuationExps extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('job_valuation_exps', function (Blueprint $table) {
            $table->bigInteger('origin_id')->nullable();
            $table->string('category')->nullable();
            $table->string('uom')->nullable();
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
            $table->dropColumn(['origin_id', 'category', 'uom']);
        });
    }
}
