<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOvertimeHrsToLabourAllocationItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('labour_allocation_items', function (Blueprint $table) {
            $table->decimal('overtime_hrs', 10, 2)->default(0);
            $table->date('period_from')->nullable();
            $table->date('period_to')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('labour_allocation_items', function (Blueprint $table) {
            $table->dropColumn(['overtime_hrs', 'period_from', 'period_to']);
        });
    }
}
