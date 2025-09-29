<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaidInvoiceItemIdToWithholdingItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('withholding_items', function (Blueprint $table) {
            $table->unsignedBigInteger('paid_invoice_item_id')->nullable()->after('paid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('withholding_items', function (Blueprint $table) {
            $table->dropColumn(['paid_invoice_item_id']);
        });
    }
}
