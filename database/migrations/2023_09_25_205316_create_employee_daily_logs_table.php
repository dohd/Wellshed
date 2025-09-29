<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeDailyLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee_daily_logs', function (Blueprint $table) {

            $table->string('edl_number')->primary();

            $table->date('date');

            $table->unsignedBigInteger('employee');

            $table->text('rating');
            $table->text('remarks');

            $table->unsignedBigInteger('reviewer');
            $table->string('reviewed_at');


            $table->timestamps();
            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employee_daily_logs');
    }
}
