<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRecipientNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recipient_notifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            //'recipient_setting_id','reference_id', 'setting_type']
            $table->unsignedBigInteger('recipient_setting_id');
            $table->unsignedBigInteger('reference_id');
            $table->text('setting_type')->nullable();
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
        Schema::dropIfExists('recipient_notifications');
    }
}
