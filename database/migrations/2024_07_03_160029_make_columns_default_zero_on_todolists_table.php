<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeColumnsDefaultZeroOnTodolistsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('todolists', function (Blueprint $table) {
            // Drop the columns
            $table->dropColumn(['task_completion', 'task_expected_percent', 'task_percent_of_expected']);
        });

        Schema::table('todolists', function (Blueprint $table) {
            // Recreate the columns with the desired attributes
            $table->decimal('task_completion', 8, 2)->unsigned()->default(0.00);
            $table->decimal('task_expected_percent', 8, 2)->unsigned()->default(0.00);
            $table->decimal('task_percent_of_expected', 8, 2)->unsigned()->default(0.00);
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
            // Drop the columns if they exist
            $table->dropColumn(['task_completion', 'task_expected_percent', 'task_percent_of_expected']);
        });

        Schema::table('todolists', function (Blueprint $table) {
            // Recreate the columns without default values
            $table->decimal('task_completion', 8, 2)->unsigned();
            $table->decimal('task_expected_percent', 8, 2)->unsigned();
            $table->decimal('task_percent_of_expected', 8, 2)->unsigned();
        });
    }
}
