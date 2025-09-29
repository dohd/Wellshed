<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToLabourAllocations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('labour_allocations', function (Blueprint $table) {
            $table->unsignedBigInteger('task_id')->nullable()->after('project_milestone');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('labour_allocations', function (Blueprint $table) {
            $table->dropColumn('task_id');
        });
    }
}
