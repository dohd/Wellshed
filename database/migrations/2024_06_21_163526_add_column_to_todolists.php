<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToTodolists extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('todolists', function (Blueprint $table) {
            $table->unsignedBigInteger('project_id')->nullable()->after('id');
            $table->unsignedBigInteger('milestone_id')->nullable()->after('project_id');
            $table->unsignedDecimal('task_completion')->nullable()->after('priority');
            $table->unsignedDecimal('task_expected_percent')->nullable()->after('task_completion');
            $table->unsignedDecimal('task_percent_of_expected')->nullable()->after('task_expected_percent');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('todolists', function (Blueprint $table) {
            //
        });
    }
}
