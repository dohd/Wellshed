<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSendEmailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('send_emails', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('subject')->nullable();
            $table->text('text_email')->nullable();
            $table->text('user_ids')->nullable();
            $table->longText('user_emails')->nullable();
            $table->date('schedule_date');
            $table->enum('user_type',['employee','customer','supplier','labourer','prospect','others'])->defaultValue('employee');
            $table->enum('delivery_type',['now','schedule'])->defaultValue('now');
            $table->enum('status',['sent','not_sent'])->defaultValue('sent');
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
        Schema::dropIfExists('send_emails');
    }
}
