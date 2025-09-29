<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToProjectMilestones extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('project_milestones', function (Blueprint $table) {
            $table->unsignedDecimal('milestone_completion')->default(0.00)->after('due_date');
            $table->unsignedDecimal('milestone_expected_percent')->default(0.00)->after('milestone_completion');
            $table->unsignedDecimal('milestone_percent_of_expected')->default(0.00)->after('milestone_expected_percent');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('project_milestones', function (Blueprint $table) {
            //
        });
    }
}
