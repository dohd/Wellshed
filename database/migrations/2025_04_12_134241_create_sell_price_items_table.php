<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSellPriceItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sell_price_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('sell_price_id');
            $table->unsignedBigInteger('import_request_item_id');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->decimal('landed_price', 22,4)->default(0);
            $table->decimal('minimum_selling_price', 22,4)->default(0);
            $table->decimal('recommended_selling_price', 22,4)->default(0);
            $table->decimal('moq', 22,4)->default(0);
            $table->decimal('reorder_level', 22,4)->default(0);
            $table->unsignedBigInteger('ins')->nullable();
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
        Schema::dropIfExists('sell_price_items');
    }
}
