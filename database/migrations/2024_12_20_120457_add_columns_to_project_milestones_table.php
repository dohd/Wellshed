<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToProjectMilestonesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('project_milestones', function (Blueprint $table) {
            $table->dateTime('start_date')->nullable()->after('color');
            $table->dateTime('end_date')->nullable()->after('start_date');
        });

        DB::table('permissions')->insert([
            'name' => 'estimate-buying-price',
            'display_name' => 'Quote Estimated Buying Price Permission',
            'module_id' => 3,
            'sort' => 0,
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('project_milestones', function (Blueprint $table) {
            $table->dropColumn(['start_date', 'end_date']);
        });
    }
}
