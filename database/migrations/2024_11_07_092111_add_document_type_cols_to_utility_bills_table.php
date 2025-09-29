<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDocumentTypeColsToUtilityBillsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('utility_bills', function (Blueprint $table) {
            $table->unsignedBigInteger('purchase_id')->nullable();
            $table->unsignedBigInteger('grn_id')->nullable();
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
            $table->dropColumn(['purchase_id', 'grn_id']);
        });
    }
}
