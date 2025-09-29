<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateThirdPartiesPromoCodeReservationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('third_parties_promo_code_reservations', function (Blueprint $table) {

            $table->uuid('uuid')->primary();

            // Link to the promotional code
            $table->bigInteger('promo_code_id');
            $table->foreign('promo_code_id')->references('id')->on('promotional_codes')->onDelete('cascade');

            $table->enum('tier', [1, 2, 3])->default(1);

            // Details of the third party
            $table->string('name'); // e.g., email, phone, or social media handle
            $table->string('organization')->nullable(); // e.g., email, phone, or social media handle
            $table->string('phone'); // e.g., email, phone, or social media handle
            $table->string('email'); // e.g., email, phone, or social media handle

            // Link to the customer
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');

            $table->text('message');

            // Status of the reservation
            $table->enum('status', ['reserved', 'used', 'expired', 'cancelled'])->default('reserved');

            $table->unsignedInteger('reserved_by')->nullable(); // Employee who reserved it
            $table->foreign('reserved_by')->references('id')->on('users')->onDelete('set null');

            // Reservation details
            $table->dateTime('reserved_at'); // When it was reserved
            $table->dateTime('expires_at'); // When it expires

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
        Schema::dropIfExists('third_parties_promo_code_reservations');
    }
}
