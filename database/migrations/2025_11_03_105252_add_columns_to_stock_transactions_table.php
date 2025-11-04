<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToStockTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stock_transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('product_category_id')->nullable()->after('price');
            $table->unsignedBigInteger('adjustment_id')->nullable()->after('product_category_id');
            $table->unsignedBigInteger('transfer_id')->nullable()->after('adjustment_id');
            $table->unsignedBigInteger('dispatch_id')->nullable()->after('transfer_id');
            $table->unsignedBigInteger('created_by')->nullable()->after('dispatch_id');
            $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
            $table->unsignedBigInteger('deleted_by')->nullable()->after('updated_by');
            $table->unsignedBigInteger('dispatch_item_id')->nullable()->after('deleted_by');
            $table->timestamp('deleted_at')->nullable()->after('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stock_transactions', function (Blueprint $table) {
            //
        });
    }
}
