<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEfrisGoodsAdjLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('efris_goods_adj_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('productvar_id');
            $table->decimal('item_qty', 10, 2)->default(0);
            $table->decimal('stock_qty', 10, 2)->default(0);
            $table->bigInteger('purchase_id')->nullable();
            $table->bigInteger('purchase_item_id')->nullable();
            $table->bigInteger('grn_id')->nullable();
            $table->bigInteger('grn_item_id')->nullable();
            $table->bigInteger('op_stock_id')->nullable();
            $table->bigInteger('op_stock_item_id')->nullable();
            $table->bigInteger('issue_id')->nullable();
            $table->bigInteger('issue_item_id')->nullable();
            $table->bigInteger('stock_adj_id')->nullable();
            $table->bigInteger('stock_adj_item_id')->nullable();
            $table->bigInteger('ins');
            $table->bigInteger('user_id');
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
        Schema::dropIfExists('efris_goods_adj_logs');
    }
}
