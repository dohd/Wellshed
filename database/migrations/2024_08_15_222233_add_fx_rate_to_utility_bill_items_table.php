<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFxRateToUtilityBillItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('utility_bill_items', function (Blueprint $table) {
            $table->decimal('fx_rate', 16, 4)->default(0)->after('total');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('utility_bill_items', function (Blueprint $table) {
            $table->dropColumnn(['fx_rate']);
        });
    }
}
