<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSendSmsesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('send_smses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->longText('subject');
            $table->longText('phone_numbers');
            $table->date('scheduled_date');
            $table->enum('delivery_type',['now','schedule'])->default('now');
            $table->enum('message_type',['bulk','single'])->defaultValue('single');
            $table->enum('user_type',['employee','customer','supplier','labourer','others'])->defaultValue('employee');
            $table->longText('sent_to_ids');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('ins');
            // $table->foreign('ins')->references('id')->on('companies');
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
        Schema::dropIfExists('send_smses');
    }
}
