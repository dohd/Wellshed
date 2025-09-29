<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateOmniChatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->down();
        Schema::create('omni_chats', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('fb_id')->nullable();
            $table->string('bot_key')->nullable();
            $table->string('username')->nullable();
            $table->string('botname')->nullable();
            $table->string('url')->nullable();
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
        Schema::dropIfExists('omni_messages');
        Schema::dropIfExists('omni_chats');
    }
}
