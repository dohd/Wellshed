<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateBillItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Using raw SQL queries to modify the columns
        DB::statement('ALTER TABLE rose_bill_items CHANGE item_purchase_class purchase_class_budget BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE rose_bill_items DROP COLUMN asset_purchase_class');
        DB::statement('ALTER TABLE rose_bill_items DROP COLUMN item_milestone');

        // Adding the foreign key constraint
        Schema::table('bill_items', function ($table) {
            $table->foreign('purchase_class_budget')
                ->references('id')
                ->on('purchase_class_budgets')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Reverting the changes using raw SQL
        Schema::table('bill_items', function ($table) {
            $table->dropForeign(['purchase_class_budget']);
        });

        DB::statement('ALTER TABLE rose_bill_items CHANGE purchase_class_budget item_purchase_class INTEGER');
        DB::statement('ALTER TABLE rose_bill_items ADD COLUMN asset_purchase_class VARCHAR(255) NULL');
        DB::statement('ALTER TABLE rose_bill_items ADD COLUMN item_milestone VARCHAR(255) NULL');
    }
}