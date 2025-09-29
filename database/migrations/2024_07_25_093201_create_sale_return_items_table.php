<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSaleReturnItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sale_return_items', function (Blueprint $table) {
            $table->unsignedBigInteger('id', true);
            $table->unsignedBigInteger('sale_return_id')->nullable();
            $table->unsignedBigInteger('verified_item_id')->nullable();
            $table->unsignedBigInteger('productvar_id')->nullable();
            $table->unsignedBigInteger('warehouse_id')->nullable();
            $table->enum('status', ['new', 'used', 'damaged', 'defective'])->nullable();
            $table->decimal('qty_onhand', 16, 2)->default(0);
            $table->decimal('return_qty', 16, 2)->default(0);
            $table->decimal('new_qty', 16, 2)->default(0);
            $table->decimal('cost', 16, 2)->default(0);
            $table->decimal('amount', 16, 2)->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sale_return_items');
    }
}
