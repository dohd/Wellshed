<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOmniMediaBlocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('omni_media_blocks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('ins');
            $table->string('name');
            $table->string('text');
            $table->bigInteger('omni_id')->nullable();
            $table->bigInteger('omni_template_id')->nullable();
            $table->bigInteger('omni_bot_id')->nullable();
            $table->integer('omni_generic')->nullable();
            $table->integer('omni_status')->nullable();
            $table->string('omni_type')->nullable();
            $table->string('omni_language')->nullable();
            $table->text('omni_data')->nullable();
            $table->string('omni_params')->nullable();
            $table->string('omni_rejected_reason')->nullable();
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
        Schema::dropIfExists('omni_media_blocks');
    }
}
