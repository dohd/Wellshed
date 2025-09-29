<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakePromoWhatsappNumbersNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Make the 'whatsapp_number' column nullable in 'customers_promo_code_reservations' table
        DB::statement('ALTER TABLE rose_customers_promo_code_reservations MODIFY COLUMN whatsapp_number VARCHAR(255) NULL;');

        // Make the 'whatsapp_number' column nullable in 'third_parties_promo_code_reservations' table
        DB::statement('ALTER TABLE rose_third_parties_promo_code_reservations MODIFY COLUMN whatsapp_number VARCHAR(255) NULL;');

        // Make the 'whatsapp_number' column nullable in 'referral_promo_reservations' table
        DB::statement('ALTER TABLE rose_referral_promo_reservations MODIFY COLUMN whatsapp_number VARCHAR(255) NULL;');

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
