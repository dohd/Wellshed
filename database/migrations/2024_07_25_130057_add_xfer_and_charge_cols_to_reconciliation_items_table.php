<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddXferAndChargeColsToReconciliationItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reconciliation_items', function (Blueprint $table) {
            $table->unsignedBigInteger('bank_transfer_id')->nullable()->after('journal_item_id');
            $table->unsignedBigInteger('charge_id')->nullable()->after('bank_transfer_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reconciliation_items', function (Blueprint $table) {
            $table->dropColumn(['bank_transfer_id', 'charge_id']);
        });
    }
}
