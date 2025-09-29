<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToStockRcvItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stock_rcv_items', function (Blueprint $table) {
            $table->decimal('issued_qty',16,4)->defaultValue(0)->after('qty_rcv');
            $table->unsignedBigInteger('item_id')->nullable()->after('issued_qty');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stock_rcv_items', function (Blueprint $table) {
            $table->dropColumn(['issued_qty', 'item_id']);
        });
    }
}
