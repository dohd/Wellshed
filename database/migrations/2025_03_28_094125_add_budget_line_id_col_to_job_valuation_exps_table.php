<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBudgetLineIdColToJobValuationExpsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('job_valuation_exps', function (Blueprint $table) {
            $table->bigInteger('budget_line_id')->nullable();
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
            $table->dropColumn(['budget_line_id']);
        });
    }
}
