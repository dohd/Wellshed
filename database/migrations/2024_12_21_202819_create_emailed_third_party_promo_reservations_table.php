<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmailedThirdPartyPromoReservationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('emailed_third_party_promo_reservations', function (Blueprint $table) {

            $table->bigIncrements('id');

            $table->unsignedBigInteger('email_id');
            $table->foreign('email_id')->references('id')->on('recent_customer_emails');

            $table->uuid('reservation_uuid');
            $table->foreign('reservation_uuid')->references('uuid')->on('third_parties_promo_code_reservations');

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('emailed_third_party_promo_reservations');
    }
}
