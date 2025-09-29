<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOtherColumnsToTodolistRelations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('todolist_relations', function (Blueprint $table) {
            $table->bigInteger('labour_id')->after('id');
            $table->foreign('labour_id')->references('id')->on('labour_allocations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('todolist_relations', function (Blueprint $table) {
            $table->dropForeign(['labour_id']);
            $table->dropColumn('labour_id');
        });
    }
}
