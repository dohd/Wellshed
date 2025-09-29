<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmsCallbacksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sms_callbacks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('delivery_status');
            $table->timestamp('delivery_time');
            $table->string('reference');
            $table->string('msisdn'); // Store the mobile number
            $table->decimal('cost', 8, 2); // Store the cost with 2 decimal places
            $table->string('sender');
            $table->unsignedBigInteger('ins');
            $table->unsignedBigInteger('user_id');
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
        Schema::dropIfExists('sms_callbacks');
    }
}
