<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEtimsColsToCreditNote extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('credit_notes', function (Blueprint $table) {
            $table->string('digitax_id', 199)->nullable();
            $table->string('etims_url', 255)->nullable();
            $table->string('sale_detail_url', 255)->nullable();
            $table->enum('queue_status', ['queued', 'un_queued', 'in_progress', 'completed', 'failed', 'paused', 'submitted'])->nullable();
            $table->string('etims_qrcode', 199)->nullable();
            $table->integer('original_invoice_number')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('credit_notes', function (Blueprint $table) {
            $table->dropColumn(['digitax_id', 'etims_url', 'sale_detail_url', 'queue_status', 'etims_qrcode', 'original_invoice_number']);
        });
    }
}
