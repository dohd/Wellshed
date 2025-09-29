<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateBudgetColumnInPurchaseClassesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchase_class_budgets', function (Blueprint $table) {
            // Change the column to have a default value of 0.00
            $table->decimal('budget', 10, 2)->default(0.00)->change();
        });

        // Update existing NULL values to 0.00
        DB::table('purchase_class_budgets')
            ->whereNull('budget')
            ->update(['budget' => 0.00]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchase_class_budgets', function (Blueprint $table) {
            // If you need to revert the change, specify the previous column definition
            $table->decimal('budget', 10, 2)->change();
        });
    }
}