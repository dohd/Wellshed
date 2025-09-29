<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExpAccountIdToCasualLabourersRemunerationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('casual_labourers_remunerations', function (Blueprint $table) {
            $table->bigInteger('exp_account_id')->nullable();
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
            $table->dropColumn(['exp_account_id']);
        });
    }
}
