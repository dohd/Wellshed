<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeAppraisalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('employee_appraisals', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedInteger('employee_id');
            $table->foreign('employee_id')->references('id')->on('users');

            $table->unsignedInteger('supervisor_id');
            $table->foreign('supervisor_id')->references('id')->on('users');

            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedTinyInteger('job_knowledge');
            $table->unsignedTinyInteger('quality_of_work');
            $table->unsignedTinyInteger('communication');
            $table->unsignedTinyInteger('attendance');
            $table->text('supervisor_comments')->nullable();
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
        Schema::dropIfExists('employee_appraisals');
    }
}
