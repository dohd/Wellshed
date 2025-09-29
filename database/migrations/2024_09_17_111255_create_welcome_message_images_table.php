<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWelcomeMessageImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('welcome_message_images', function (Blueprint $table) {

            $table->bigIncrements('id');

            $table->unsignedBigInteger('welcome_message_id');
            $table->foreign('welcome_message_id')->references('id')->on('welcome_messages');

            $table->string('location');
            $table->string('filename');

            $table->unsignedInteger('ins');
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
        Schema::dropIfExists('welcome_message_images');
    }
}
