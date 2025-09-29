<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignCurrColsToGrnItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('goods_receive_note_items', function (Blueprint $table) {
            $table->decimal('fx_rate', 16, 4)->default(0)->after('tax_rate');
            $table->decimal('fx_subtotal', 16, 4)->default(0)->after('fx_rate');
            $table->decimal('fx_taxable', 16, 4)->default(0)->after('fx_subtotal');
            $table->decimal('fx_tax', 16, 4)->default(0)->after('fx_taxable');
            $table->decimal('fx_amount', 16, 4)->default(0)->after('fx_tax');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('goods_receive_note_items', function (Blueprint $table) {
            $table->dropColumn(['fx_rate', 'fx_subtotal', 'fx_taxable', 'fx_tax', 'fx_amount']);
        });
    }
}
