<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaymentDateToCustomerEnrollmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customer_enrollments', function (Blueprint $table) {
            $table->date('payment_date')->nullable()->after('payment_status');
            $table->text('payment_note')->nullable()->after('payment_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customer_enrollments', function (Blueprint $table) {
            $table->dropColumn(['payment_date','payment_note']);
        });
    }
}
