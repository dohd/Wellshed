<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmsResponsesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sms_responses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('message_response_id');
            $table->boolean('status');
            $table->string('response_code');
            $table->enum('message_type', ['single','bulk']);
            $table->decimal('phone_number_count')->defaultValue(0);
            $table->unsignedBigInteger('ins');
            // $table->foreign('ins')->references('id')->on('companies');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('send_sms_id');
            $table->foreign('send_sms_id')->references('id')->on('send_smses');
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
        Schema::dropIfExists('sms_responses');
    }
}
