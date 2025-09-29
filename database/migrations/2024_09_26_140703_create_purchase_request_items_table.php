<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseRequestItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_request_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('purchase_request_id');
            $table->foreign('purchase_request_id')->references('id')->on('purchase_requests');
            $table->text('product_name')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('milestone_item_id')->nullable();
            $table->unsignedBigInteger('unit_id')->nullable();
            $table->decimal('qty', 16,4)->defaultValue(0);
            $table->decimal('price', 16,4)->defaultValue(0);
            $table->unsignedInteger('ins');
            $table->foreign('ins')->references('id')->on('companies');
            $table->unsignedBigInteger('user_id');
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
        Schema::dropIfExists('purchase_request_items');
    }
}
