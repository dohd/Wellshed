<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEfrisColsToCreditNotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('credit_notes', function (Blueprint $table) {
            $table->integer('efris_reason_code')->nullable();
            $table->string('efris_reason_code_name')->nullable();
            $table->string('efris_reference_no')->nullable();
            $table->unsignedbigInteger('efris_creditnote_id')->nullable();
            $table->unsignedbigInteger('efris_creditnote_no')->nullable();
            $table->string('efris_qr_code')->nullable();
            $table->string('efris_approval_status', 20)->nullable();
            $table->string('efris_approval_status_name', 20)->nullable();
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
            $table->dropColumns([
                'efris_reason_code', 'efris_reason_code_name', 'efris_reference_no',
                'efris_creditnote_no', 'efris_creditnote_id', 'efris_qr_code', 'efris_approval_status', 'efris_approval_status_name'
            ]);
        });
    }
}
