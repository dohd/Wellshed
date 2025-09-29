<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTidColToCasualLabourersRemunerations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('casual_labourers_remunerations', function (Blueprint $table) {
            $table->bigInteger('tid')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('casual_labourers_remunerations', function (Blueprint $table) {
            $table->dropColumn(['tid']);
        });
    }
}
