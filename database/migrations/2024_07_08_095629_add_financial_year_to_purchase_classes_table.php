<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFinancialYearToPurchaseClassesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchase_class_budgets', function (Blueprint $table) {

            $table->unsignedBigInteger('financial_year_id')->nullable()->after('name');
            $table->foreign('financial_year_id')->references('id')->on('financial_years');

            $table->dropColumn('start_date');
            $table->dropColumn('end_date');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchase_class_budgets', function (Blueprint $table) {

            $table->dropForeign(['financial_year_id']);
            $table->dropColumn('financial_year_id');

        });
    }
}
