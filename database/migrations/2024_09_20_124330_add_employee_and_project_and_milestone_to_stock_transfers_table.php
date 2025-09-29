<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEmployeeAndProjectAndMilestoneToStockTransfersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stock_transfers', function (Blueprint $table) {

            $table->unsignedInteger('employee_id')->nullable()->after('dest_id');
            $table->foreign('employee_id')->references('id')->on('users');

            $table->unsignedBigInteger('project_id')->nullable()->after('employee_id');
            $table->foreign('project_id')->references('id')->on('projects');

            $table->integer('project_milestone')->nullable()->after('project_id');
            $table->foreign('project_milestone')->references('id')->on('project_milestones');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stock_transfers', function (Blueprint $table) {
            //
        });
    }
}
