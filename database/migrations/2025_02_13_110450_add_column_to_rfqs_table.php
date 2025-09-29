<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToRfqsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rfqs', function (Blueprint $table) {
            // $table->dropForeign(['project_id']); // Drop foreign key
            $table->text('supplier_ids')->nullable(); // Make nullable
            $table->text('purchase_requisition_ids')->nullable(); // Make nullable
            $table->enum('status', ['pending','approved','rejected','review'])->default('pending');
            $table->string('status_note')->nullable();
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
        Schema::table('rfqs', function (Blueprint $table) {
            //
        });
    }
}
