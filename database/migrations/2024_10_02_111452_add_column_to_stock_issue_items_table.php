<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToStockIssueItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stock_issue_items', function (Blueprint $table) {
            $table->decimal('booked_qty', 16,4)->default(0)->after('qty_rem');
            $table->unsignedBigInteger('requisition_item_id')->nullable()->after('booked_qty');
            $table->unsignedBigInteger('budget_item_id')->nullable()->after('requisition_item_id');
            $table->unsignedBigInteger('item_id')->nullable()->after('budget_item_id');
            $table->unsignedBigInteger('product_item_id')->nullable()->after('item_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stock_issue_items', function (Blueprint $table) {
            //
        });
    }
}
