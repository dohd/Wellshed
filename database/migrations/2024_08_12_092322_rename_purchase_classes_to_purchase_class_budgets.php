<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenamePurchaseClassesToPurchaseClassBudgets extends Migration
{

    public function up()
    {
        // Rename the table
        DB::statement('RENAME TABLE rose_purchase_classes TO rose_purchase_class_budgets');

        // Update foreign key constraints
        DB::statement('ALTER TABLE rose_bills CHANGE purchaseClass purchase_class_budget BIGINT UNSIGNED');
        DB::statement('ALTER TABLE rose_purchase_orders CHANGE purchaseClass purchase_class_budget BIGINT UNSIGNED');
    }

    public function down()
    {

        // Rename columns and change their type back
        DB::statement('ALTER TABLE rose_bills CHANGE purchase_class_budget purchaseClass INT');
        DB::statement('ALTER TABLE rose_purchase_orders CHANGE purchase_class_budget purchaseClass INT');

        // Rename the table back to its original name
        DB::statement('RENAME TABLE rose_purchase_class_budgets TO rose_purchase_classes');
    }
}
