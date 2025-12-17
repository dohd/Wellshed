<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropUniqueConstraintFromMetaWhatsappThreads extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meta_whatsapp_threads', function (Blueprint $table) {
            $table->dropUnique('meta_whatsapp_threads_message_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('meta_whatsapp_threads', function (Blueprint $table) {
            $table->unique('message_id');
        });
    }
}
