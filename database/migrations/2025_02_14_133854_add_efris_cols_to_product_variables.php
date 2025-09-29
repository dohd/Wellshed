<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEfrisColsToProductVariables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_variables', function (Blueprint $table) {
            $table->string('efris_unit_name', 50)->nullable();
            $table->string('efris_unit', 50)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_variables', function (Blueprint $table) {
            $table->dropColumn(['efris_unit_name', 'efris_unit']);
        });
    }
}
