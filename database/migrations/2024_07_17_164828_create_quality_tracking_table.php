<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQualityTrackingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quality_tracking', function (Blueprint $table) {

            $table->bigIncrements('id');
            $table->date('date')->nullable();
            $table->bigInteger('customer_id')->nullable(false);
            $table->bigInteger('branch_id')->nullable();
            $table->bigInteger('project_id')->nullable(false);
            $table->string('employee', 1000)->nullable(false);
            $table->longText('incident_desc')->nullable(false);
            $table->longText('route_course')->nullable();
            $table->string('status', 1000)->nullable();
            $table->bigInteger('responsibility')->nullable();
            $table->bigInteger('timing')->nullable();
            $table->text('plan')->nullable();
            $table->text('do')->nullable();
            $table->text('check')->nullable();
            $table->text('act')->nullable();
            $table->longText('comments')->nullable();
            $table->text('countermeasure')->nullable();

            $table->unsignedInteger('cm_responsible_person')->nullable();
            $table->foreign('cm_responsible_person')->references('id')->on('users');

            $table->date('completion_date')->nullable();
            $table->text('verification')->nullable();
            $table->bigInteger('ins')->nullable(false);
            $table->bigInteger('user_id')->nullable(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('quality_tracking');
    }
}
