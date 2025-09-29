<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseRequisitionItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_requisition_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('purchase_requisition_id');
            $table->unsignedBigInteger('purchase_request_item_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('project_id')->nullable();
            $table->string('product_name');
            $table->unsignedBigInteger('unit_id');
            $table->decimal('qty',16,4)->default(0);
            $table->decimal('stock_qty',16,4)->default(0);
            $table->decimal('purchase_qty',16,4)->default(0);
            $table->decimal('price', 16, 2)->default(0);
            $table->unsignedBigInteger('milestone_item_id');
            $table->unsignedBigInteger('budget_item_id');
            $table->unsignedBigInteger('ins');
            $table->unsignedBigInteger('user_id');
            $table->text('remark')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchase_requisition_items');
    }
}
