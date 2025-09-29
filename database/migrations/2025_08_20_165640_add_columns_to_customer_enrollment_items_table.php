<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToCustomerEnrollmentItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customer_enrollment_items', function (Blueprint $table) {
            // $table->unsignedBigInteger('customer_enrollment_id')->nullable()->change();
            $table->unsignedBigInteger('quote_id')->nullable();
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->decimal('quote_amount', 22,2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customer_enrollment_items', function (Blueprint $table) {
            $table->dropColumn(['quote_id','invoice_id','quote_amount']);
        });
    }
}
