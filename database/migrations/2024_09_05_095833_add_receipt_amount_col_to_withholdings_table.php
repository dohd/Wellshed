<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReceiptAmountColToWithholdingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('withholdings', function (Blueprint $table) {
            $table->decimal('receipt_amount', 16, 4)->default(0)->after('certificate');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('withholdings', function (Blueprint $table) {
            $table->dropColumn(['receipt_amount']);
        });
    }
}
