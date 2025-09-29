<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWithholdingColsToPaidInvoiceItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('paid_invoice_items', function (Blueprint $table) {
            $table->decimal('wh_tax', 16, 4)->default(0)->after('invoice_id');
            $table->decimal('wh_vat', 16, 4)->default(0)->after('wh_tax');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('paid_invoice_items', function (Blueprint $table) {
            $table->dropColumn(['wh_tax', 'wh_vat']);
        });
    }
}
