<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLabourAllocationIdToClrWages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('clr_wages', function (Blueprint $table) {
            $table->bigInteger('labour_allocation_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('clr_wages', function (Blueprint $table) {
            $table->dropColumn(['labour_allocation_id']);
        });
    }
}
