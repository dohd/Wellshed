<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToRfqItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rfq_items', function (Blueprint $table) {
            // $table->unsignedBigInteger('project_id')->nullable();
            $table->unsignedBigInteger('project_milestone_id')->nullable();
            $table->unsignedBigInteger('purchase_requisition_item_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rfq_items', function (Blueprint $table) {
            //
        });
    }
}
