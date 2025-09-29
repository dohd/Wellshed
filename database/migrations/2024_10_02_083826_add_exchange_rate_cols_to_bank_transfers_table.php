<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExchangeRateColsToBankTransfersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bank_transfers', function (Blueprint $table) {
            $table->decimal('source_rate', 16, 4)->default(0);
            $table->decimal('dest_rate', 16, 4)->default(0);
            $table->decimal('source_amount_fx', 16, 4)->default(0);
            $table->decimal('dest_amount_fx', 16, 4)->default(0);
            $table->decimal('fx_gain_total', 16, 4)->default(0);
            $table->decimal('fx_loss_total', 16, 4)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bank_transfers', function (Blueprint $table) {
            $table->dropColumn(['source_rate', 'dest_rate', 'source_amount_fx', 'dest_amount_fx', 'fx_gain_total', 'fx_loss_total']);
        });
    }
}
