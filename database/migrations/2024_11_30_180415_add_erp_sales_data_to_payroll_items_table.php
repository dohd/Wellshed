<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddErpSalesDataToPayrollItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payroll_items', function (Blueprint $table) {

            $table->decimal('erp_sales_count', 22, 2)->default(0.00);
            $table->decimal('erp_sales_value', 22, 2)->default(0.00);
            $table->decimal('erp_sales_rate', 22, 2)->default(0.00);
            $table->decimal('erp_sales_commission', 22, 2)->default(0.00);

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
            //
        });
    }
}
