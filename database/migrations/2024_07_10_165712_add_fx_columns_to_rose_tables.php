<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFxColumnsToRoseTables extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add columns to invoices table
        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('fx_curr_rate', 22, 2)->nullable();
            $table->decimal('fx_taxable', 22, 2)->nullable();
            $table->decimal('fx_subtotal', 22, 2)->nullable();
            $table->decimal('fx_tax', 22, 2)->nullable();
            $table->decimal('fx_total', 22, 2)->nullable();
        });

        // Add columns to transactions table
        Schema::table('transactions', function (Blueprint $table) {
            $table->decimal('fx_curr_rate', 22, 2)->nullable();
            $table->decimal('fx_debit', 22, 2)->nullable();
            $table->decimal('fx_credit', 22, 2)->nullable();
        });

        // Add columns to invoice_items table
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->decimal('fx_curr_rate', 22, 2)->nullable();
            $table->decimal('fx_product_tax', 22, 2)->nullable();
            $table->decimal('fx_product_price', 22, 2)->nullable();
            $table->decimal('fx_product_subtotal', 22, 2)->nullable();
            $table->decimal('fx_product_amount', 22, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Remove columns from invoices table
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['fx_curr_rate', 'fx_taxable', 'fx_subtotal', 'fx_tax', 'fx_total']);
        });

        // Remove columns from transactions table
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['fx_curr_rate', 'fx_debit', 'fx_credit']);
        });

        // Remove columns from invoice_items table
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropColumn(['fx_curr_rate', 'fx_product_tax', 'fx_product_price', 'fx_product_subtotal', 'fx_product_amount']);
        });
    }
}
