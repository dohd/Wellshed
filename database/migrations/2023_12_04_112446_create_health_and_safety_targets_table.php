<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHealthAndSafetyTargetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('health_and_safety_targets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->year('year')->unique();
            $table->unsignedBigInteger('jan')->unique();
            $table->unsignedBigInteger('feb')->unique();
            $table->unsignedBigInteger('march')->unique();
            $table->unsignedBigInteger('april')->unique();
            $table->unsignedBigInteger('may')->unique();
            $table->unsignedBigInteger('june')->unique();
            $table->unsignedBigInteger('july')->unique();
            $table->unsignedBigInteger('aug')->unique();
            $table->unsignedBigInteger('sept')->unique();
            $table->unsignedBigInteger('oct')->unique();
            $table->unsignedBigInteger('nov')->unique();
            $table->unsignedBigInteger('dec')->unique();
            $table->unsignedBigInteger('ins')->unique();
            $table->unsignedBigInteger('user_id')->unique();
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
        Schema::dropIfExists('health_and_safety_targets');
    }
}
