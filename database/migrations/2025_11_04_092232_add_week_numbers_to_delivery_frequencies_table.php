<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWeekNumbersToDeliveryFrequenciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('delivery_frequencies', function (Blueprint $table) {
            $table->json('delivery_days')->nullable()->change();
            // Add week_numbers for custom frequency
            $table->json('week_numbers')->nullable()->after('delivery_days');
             $table->json('locations_for_days')->nullable();
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
            //
        });
    }
}
