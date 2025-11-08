<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddQtyPerDayToDeliveryFrequenciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('delivery_frequencies', function (Blueprint $table) {
            $table->json('qty_per_day')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('delivery_frequencies', function (Blueprint $table) {
            $table->dropColumn('qty_per_day');
        });
    }
}
