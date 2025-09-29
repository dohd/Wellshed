<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPartItemIdColumnToPurchaseRequisitionItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchase_requisition_items', function (Blueprint $table) {
            $table->unsignedBigInteger('part_item_id')->nullable();
            $table->unsignedBigInteger('milestone_item_id')->nullable()->change();
            $table->unsignedBigInteger('budget_item_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchase_requisition_items', function (Blueprint $table) {
            $table->dropColumn('part_item_id');
            $table->unsignedBigInteger('milestone_item_id')->nullable(false)->change();
            $table->unsignedBigInteger('budget_item_id')->nullable(false)->change();
        });
    }
}
