<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddValuedBalanceColToJobValuationExps extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('job_valuation_exps', function (Blueprint $table) {
            $table->decimal('valued_bal', 16,4)->default(0);
            $table->bigInteger('expitem_id')->nullable();
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
            $table->dropColumn(['valued_bal', 'expitem_id']);
        });
    }
}
