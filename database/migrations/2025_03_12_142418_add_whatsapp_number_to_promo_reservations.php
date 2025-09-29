<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWhatsappNumberToPromoReservations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customers_promo_code_reservations', function (Blueprint $table) {

            $table->string('whatsapp_number')->after('phone');
        });

        Schema::table('third_parties_promo_code_reservations', function (Blueprint $table) {

            $table->string('whatsapp_number')->after('phone');
        });

        Schema::table('referral_promo_reservations', function (Blueprint $table) {

            $table->string('whatsapp_number')->after('phone');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('promo_reservations', function (Blueprint $table) {
            //
        });
    }
}
