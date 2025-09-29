<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDescrColumnToCasualLabourersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('casual_labourers', function (Blueprint $table) {
            $table->string('home_county')->nullable();
            $table->longText('casual_description')->nullable();
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
            $table->dropColumn(['home_county','casual_description']);
        });
    }
}
