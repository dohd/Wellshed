<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAddressColToCustomerZoneItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customer_zone_items', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_address_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customer_zone_items', function (Blueprint $table) {
            $table->dropColumn(['customer_address_id']);
        });
    }
}
