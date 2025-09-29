<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdatePurchaseOrderItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Using raw SQL to rename the column and change its type
        DB::statement('ALTER TABLE rose_purchase_order_items CHANGE item_purchase_class purchase_class_budget BIGINT UNSIGNED NULL');

        // Adding the foreign key constraint
        Schema::table('purchase_order_items', function ($table) {
            $table->foreign('purchase_class_budget')
                ->references('id')
                ->on('purchase_class_budgets');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Dropping the foreign key constraint
        Schema::table('purchase_order_items', function ($table) {
            $table->dropForeign(['purchase_class_budget']);
        });

        // Reverting the column change using raw SQL
        DB::statement('ALTER TABLE rose_purchase_order_items CHANGE purchase_class_budget item_purchase_class INTEGER NOT NULL');
    }
}