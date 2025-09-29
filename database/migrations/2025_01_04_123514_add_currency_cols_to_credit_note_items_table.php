<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCurrencyColsToCreditNoteItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('credit_note_items', function (Blueprint $table) {
            $table->decimal('prod_fx_rate', 16, 4)->default(0);
            $table->decimal('prod_fx_subtotal', 16, 4)->default(0);
            $table->decimal('prod_fx_taxable', 16, 4)->default(0);
            $table->decimal('prod_fx_tax', 16, 4)->default(0);
            $table->decimal('prod_fx_total', 16, 4)->default(0);
            $table->decimal('prod_fx_gain', 16, 4)->default(0);
            $table->decimal('prod_fx_loss', 16, 4)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('credit_note_items', function (Blueprint $table) {
            $table->dropColumn(['prod_fx_rate', 'prod_fx_subtotal', 'prod_fx_taxable', 'prod_fx_tax', 'prod_fx_total', 'prod_fx_gain', 'prod_fx_loss']);
        });
    }
}
