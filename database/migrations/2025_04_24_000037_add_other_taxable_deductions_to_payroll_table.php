<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOtherTaxableDeductionsToPayrollTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payroll', function (Blueprint $table) {

            $table->decimal('other_taxable_deductions', 22, 2)->nullable();
            $table->dropColumn('deduction_total');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payroll', function (Blueprint $table) {

            $table->decimal('deduction_total', 22, 2);
            $table->dropColumn('other_taxable_deductions');

        });
    }
}
