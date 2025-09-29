<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRecipientSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recipient_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('title')->nullable();
            $table->enum('type',['project','invoice','stock','leave','document']);
            $table->enum('uom',['%','AMOUNT','QTY','DATE']);
            $table->enum('email',['yes','no'])->default('no');
            $table->enum('sms',['yes','no'])->default('no');
            $table->string('target')->nullable();
            $table->text('recipients')->nullable();
            $table->unsignedBigInteger('latest_project_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('ins');
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
        Schema::dropIfExists('recipient_settings');
    }
}
