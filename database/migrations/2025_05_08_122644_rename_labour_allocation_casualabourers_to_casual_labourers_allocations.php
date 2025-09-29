<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class RenameLabourAllocationCasualabourersToCasualLabourersAllocations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename('labour_allocation_casual_labourers', 'casual_labourers_allocations');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::rename('casual_labourers_allocations', 'labour_allocation_casual_labourers');
    }
}
