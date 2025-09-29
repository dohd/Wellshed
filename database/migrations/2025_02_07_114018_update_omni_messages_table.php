<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateOmniMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->down();
        Schema::create('omni_messages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('omni_chat_id')->nullable();
            $table->string('username')->nullable();
            $table->text('message')->nullable();
            $table->timestamp('date')->nullable();
            $table->unsignedBigInteger('payload_id')->nullable();
            $table->string('bot_id')->nullable();
            $table->string('from')->nullable();
            $table->string('from_user_id')->nullable();
            $table->unsignedBigInteger('ins')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
            $table->foreign('omni_chat_id')->references('id')->on('omni_chats')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('omni_messages');
    }
}
