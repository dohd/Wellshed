<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEnvironmentalTrackingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('environmental_tracking', function (Blueprint $table) {

            $table->bigIncrements('id');
            $table->date('date')->nullable();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('project_id');
            $table->string('employee', 1000);
            $table->longText('incident_desc');
            $table->longText('route_course')->nullable();
            $table->string('status', 1000)->nullable();
            $table->unsignedBigInteger('responsibility')->nullable();
            $table->unsignedBigInteger('timing')->nullable();
            $table->text('plan')->nullable();
            $table->text('do')->nullable();
            $table->text('check')->nullable();
            $table->text('act')->nullable();
            $table->longText('comments')->nullable();
            $table->text('countermeasure')->nullable();
            $table->unsignedInteger('cm_responsible_person')->nullable();
            $table->date('completion_date')->nullable();
            $table->text('verification')->nullable();

            $table->unsignedInteger('ins');
            $table->foreign('ins')->references('id')->on('companies');

            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            $table->foreign('cm_responsible_person')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->index('cm_responsible_person');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('environmental_tracking');
    }
}
