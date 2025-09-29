<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCurrencyColsToCreditNotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('credit_notes', function (Blueprint $table) {
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->decimal('fx_curr_rate', 16, 4)->default(0);
            $table->decimal('fx_subtotal', 16, 4)->default(0);
            $table->decimal('fx_taxable', 16, 4)->default(0);
            $table->decimal('fx_tax', 16, 4)->default(0);
            $table->decimal('fx_total', 16, 4)->default(0);
            $table->decimal('fx_gain', 16, 4)->default(0);
            $table->decimal('fx_loss', 16, 4)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('credit_notes', function (Blueprint $table) {
            $table->dropColumn(['currency_id', 'fx_curr_rate', 'fx_subtotal', 'fx_taxable', 'fx_tax', 'fx_total', 'fx_gain', 'fx_loss']);
        });
    }
}
