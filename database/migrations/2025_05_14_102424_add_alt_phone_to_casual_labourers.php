<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAltPhoneToCasualLabourers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('casual_labourers', function (Blueprint $table) {
            $table->string('alt_phone_number', 199)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('casual_labourers', function (Blueprint $table) {
            $table->dropColumn(['alt_phone_number']);
        });
    }
}
