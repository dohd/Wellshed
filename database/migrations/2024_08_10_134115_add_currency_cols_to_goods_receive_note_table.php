<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCurrencyColsToGoodsReceiveNoteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('goods_receive_notes', function (Blueprint $table) {
            $table->unsignedBigInteger('currency_id')->nullable()->after('purchaseorder_id');
            $table->decimal('fx_curr_rate', 16, 4)->default(0)->after('currency_id');
            $table->decimal('fx_tax', 16, 4)->default(0)->after('total');
            $table->decimal('fx_subtotal', 16, 4)->default(0)->after('fx_tax');
            $table->decimal('fx_total', 16, 4)->default(0)->after('fx_subtotal');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('goods_receive_notes', function (Blueprint $table) {
            $table->dropColumn(['currency_id', 'fx_curr_rate', 'fx_tax', 'fx_subtotal', 'fx_total']);
        });
    }
}
