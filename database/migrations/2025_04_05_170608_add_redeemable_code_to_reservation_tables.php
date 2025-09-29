<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRedeemableCodeToReservationTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('referral_promo_reservations', function (Blueprint $table) {

            $table->string('redeemable_code', 10)->unique()->after('uuid');
        });

        Schema::table('third_parties_promo_code_reservations', function (Blueprint $table) {

            $table->string('redeemable_code', 10)->unique()->after('uuid');
        });

        Schema::table('customers_promo_code_reservations', function (Blueprint $table) {

            $table->string('redeemable_code', 10)->unique()->after('uuid');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reservation_tables', function (Blueprint $table) {
            //
        });
    }
}
