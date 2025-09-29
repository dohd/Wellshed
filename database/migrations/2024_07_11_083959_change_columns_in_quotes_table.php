<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeColumnsInQuotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->decimal('verified_amount', 22, 2)->default(0.00)->change();
            $table->decimal('verified_tax', 22, 2)->default(0.00)->change();
            $table->decimal('verified_taxable', 22, 2)->default(0.00)->change();
            $table->decimal('verified_total', 22, 2)->default(0.00)->change();
            $table->decimal('subtotal', 22, 2)->default(0.00)->change();
            $table->decimal('tax', 22, 2)->default(0.00)->change();
            $table->decimal('taxable', 22, 2)->default(0.00)->change();
            $table->decimal('total', 22, 2)->default(0.00)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('quotes', function (Blueprint $table) {
            // Change the columns back to their previous state
            // Update the below lines to match the original state if needed
            $table->decimal('verified_amount', 8, 2)->change();
            $table->decimal('verified_tax', 8, 2)->change();
            $table->decimal('verified_taxable', 8, 2)->change();
            $table->decimal('verified_total', 8, 2)->change();
            $table->decimal('subtotal', 8, 2)->change();
            $table->decimal('tax', 8, 2)->change();
            $table->decimal('taxable', 8, 2)->change();
            $table->decimal('total', 8, 2)->change();
        });
    }
}
