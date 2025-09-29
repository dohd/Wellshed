<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveLabourAllocationIdFromCasualLabourersRemunerationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('casual_labourers_remunerations', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['labour_allocation_id']);
            // Drop the column
            $table->dropColumn('labour_allocation_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('casual_labourers_remunerations', function (Blueprint $table) {
            // Re-add the column
            $table->bigInteger('labour_allocation_id');
            // Re-add the foreign key constraint
            $table->foreign('labour_allocation_id')->references('id')->on('labour_allocations');
        });
    }
}
