<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeReportFieldsNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('quality_tracking', function (Blueprint $table) {

            $table->bigInteger('customer_id')->nullable()->change();
            $table->bigInteger('project_id')->nullable()->change();
            $table->bigInteger('user_id')->nullable()->change();
            $table->string('employee')->nullable()->change();

        });

        Schema::table('health_and_safety_tracking', function (Blueprint $table) {

            $table->bigInteger('customer_id')->nullable()->change();
            $table->bigInteger('project_id')->nullable()->change();
            $table->bigInteger('user_id')->nullable()->change();
            $table->string('employee')->nullable()->change();

        });

        Schema::table('environmental_tracking', function (Blueprint $table) {

            $table->bigInteger('user_id')->nullable()->change();
            $table->string('employee')->nullable()->change();

        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
