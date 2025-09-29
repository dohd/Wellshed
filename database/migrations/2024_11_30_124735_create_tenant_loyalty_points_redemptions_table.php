<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTenantLoyaltyPointsRedemptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tenant_loyalty_points_redemptions', function (Blueprint $table) {

            $table->bigIncrements('id');
            $table->integer('tenant_id');
            $table->integer('points');
            $table->integer('days');
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
        Schema::dropIfExists('tenant_loyalty_points_redemptions');
    }
}
