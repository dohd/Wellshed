<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCurrencyColsToUtilityBillsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('utility_bills', function (Blueprint $table) {
            $table->unsignedBigInteger('currency_id')->nullable()->after('supplier_id');
            $table->decimal('fx_curr_rate', 8, 4)->default(0)->after('currency_id');
            $table->decimal('fx_subtotal', 16, 4)->default(0)->after('total');
            $table->decimal('fx_taxable', 16, 4)->default(0)->after('fx_subtotal');
            $table->decimal('fx_tax', 16, 4)->default(0)->after('fx_taxable');
            $table->decimal('fx_total', 16, 4)->default(0)->after('fx_tax');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('utility_bills', function (Blueprint $table) {
            $table->dropColumn(['currency_id', 'fx_curr_rate', 'fx_subtotal', 'fx_taxable', 'fx_tax', 'fx_total']);
        });
    }
}
