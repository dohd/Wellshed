<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeliveryItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('delivery_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('delivery_id');
            $table->unsignedBigInteger('delivery_schedule_item_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('order_item_id');
            $table->decimal('planned_qty',16,2)->default(0);
            $table->decimal('delivered_qty',16,2)->default(0);
            $table->decimal('returned_qty',16,2)->default(0);
            $table->unsignedBigInteger('ins');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('delivery_items');
    }
}
