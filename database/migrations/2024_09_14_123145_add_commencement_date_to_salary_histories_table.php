<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCommencementDateToSalaryHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('salary_histories', function (Blueprint $table) {

            $table->date('commencement_date')->nullable()->after('deduction_exempt');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('salary_histories', function (Blueprint $table) {
            $table->dropColumn('commencement_date');
        });
    }
}