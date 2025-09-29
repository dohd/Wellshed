<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToClientFeedbackTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('client_feedback', function (Blueprint $table) {
            //'redeemable_uuid','promo_code_id',
            $table->uuid('redeemable_uuid')->nullable();
            $table->unsignedBigInteger('promo_code_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('client_feedback', function (Blueprint $table) {
            $table->dropColumn(['redeemable_uuid','promo_code_id']);
        });
    }
}
