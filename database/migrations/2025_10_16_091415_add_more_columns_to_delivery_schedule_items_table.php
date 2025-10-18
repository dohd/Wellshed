<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMoreColumnsToDeliveryScheduleItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('delivery_schedule_items', function (Blueprint $table) {
            $table->decimal('returned_qty',16,2)->default(0);
            $table->decimal('delivered_qty',16,2)->default(0);
            $table->decimal('remaining_qty',16,2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('delivery_schedule_items', function (Blueprint $table) {
            $table->dropColumn(['returned_qty','delivered_qty','remaining_qty']);
        });
    }
}
