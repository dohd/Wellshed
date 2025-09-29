<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserMarqueesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_marquees', function (Blueprint $table) {

            $table->bigIncrements('id');

            $table->text('content');

            $table->datetime('start');
            $table->datetime('end');

            $table->unsignedInteger('ins')->unique();
            $table->foreign('ins')->references('id')->on('companies');

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
        Schema::dropIfExists('user_marquees');
    }
}
