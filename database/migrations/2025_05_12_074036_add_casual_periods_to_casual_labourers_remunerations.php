<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCasualPeriodsToCasualLabourersRemunerations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('casual_labourers_remunerations', function (Blueprint $table) {
            $table->date('period_from')->nullable();
            $table->date('period_to')->nullable();
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
            $table->dropColumn(['period_from', 'period_to']);
        });
    }
}
