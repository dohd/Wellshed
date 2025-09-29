<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusToImportRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('import_requests', function (Blueprint $table) {
            $table->enum('status', ['pending','approved','amend','rejected'])->default('pending');
            $table->text('status_note')->nullable();
            $table->date('approval_date')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('import_requests', function (Blueprint $table) {
            //
        });
    }
}
