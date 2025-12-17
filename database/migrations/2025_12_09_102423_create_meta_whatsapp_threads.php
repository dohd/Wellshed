<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMetaWhatsappThreads extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('meta_whatsapp_threads', function (Blueprint $table) {
            $table->bigIncrements('id');
            // Meta object info
            $table->string('object')->nullable();
            $table->string('entry_id')->nullable();
            // Metadata
            $table->string('display_phone_number')->nullable();
            $table->string('phone_number_id')->nullable();
            // Contact info
            $table->string('contact_name')->nullable();
            $table->string('wa_id')->nullable();
            // Message details
            $table->string('message_id')->unique();
            $table->string('from')->nullable();
            $table->string('timestamp')->nullable();
            $table->string('type')->nullable();
            $table->text('message_body')->nullable();
            // Status details
            $table->string('status')->nullable(); // e.g. read, delivered
            $table->string('recipient_id')->nullable();
            $table->boolean('billable')->nullable();
            $table->string('pricing_model')->nullable();
            $table->string('pricing_category')->nullable();
            $table->string('pricing_type')->nullable();

            // User association (optional if linking to your users table)
            $table->unsignedBigInteger('user_id')->nullable();
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
        Schema::dropIfExists('meta_whatsapp_threads');
    }
}
