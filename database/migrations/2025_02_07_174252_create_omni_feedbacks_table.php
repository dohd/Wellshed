<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOmniFeedbacksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('omni_feedbacks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('omni_chat_id')->nullable();
            $table->unsignedBigInteger('agent_lead_id')->nullable();
            $table->string('username')->nullable();
            $table->string('fb_id')->nullable();
            $table->string('user_id')->nullable();
            $table->string('bot_id')->nullable();
            $table->string('bot_name')->nullable();
            $table->string('form_name')->nullable();
            $table->text('raw')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->unsignedBigInteger('ins')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('omni_feedbacks');
    }
}
