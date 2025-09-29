<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdditionalDeductionsToPayrollItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payroll_items', function (Blueprint $table) {

            $table->decimal('additional_taxable_deductions', 22, 2);
            $table->dropColumn('taxable_deductions');
            $table->dropColumn('nhif_relief');
            $table->dropColumn('ahl_relief');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payroll_items', function (Blueprint $table) {


            $table->dropColumn('additional_taxable_deductions');
            $table->decimal('taxable_deductions', 22, 2);
            $table->decimal('nhif_relief', 22, 2);
            $table->decimal('ahl_relief', 22, 2);

        });
    }
}
