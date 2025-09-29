<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCustomerEnrollmentIdToCommissionItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('commission_items', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_enrollment_item_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('commission_items', function (Blueprint $table) {
            $table->dropColumn('customer_enrollment_item_id');
        });
    }
}
