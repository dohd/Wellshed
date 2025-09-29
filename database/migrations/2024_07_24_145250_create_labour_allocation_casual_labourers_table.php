<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLabourAllocationCasualLabourersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('labour_allocation_casual_labourers', function (Blueprint $table) {

            $table->string('lacl_number')->primary(); // Primary key

            $table->bigInteger('labour_allocation_id');
            $table->foreign('labour_allocation_id')->references('id')->on('labour_allocations');

            $table->unsignedBigInteger('casual_labourer_id');
            $table->foreign('casual_labourer_id')->references('id')->on('casual_labourers');

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('labour_allocation_casual_labourers');
    }
}
