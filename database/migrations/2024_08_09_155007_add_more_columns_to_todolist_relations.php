<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMoreColumnsToTodolistRelations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('todolist_relations', function (Blueprint $table) {
            $table->unsignedInteger('related')->default(0)->change();
            $table->unsignedInteger('rid')->default(0)->change();
            $table->string('description')->nullable();
            $table->date('date')->nullable();
            $table->enum('type',['increment','decrement'])->default('increment');
            $table->decimal('percent_qty')->default(0);
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
            $table->dropColumn('description');
            $table->dropColumn('date');
            $table->dropColumn('type');
            $table->dropColumn('percent_qty');
        });
    }
}
