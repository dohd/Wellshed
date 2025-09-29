<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalaryHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('salary_histories', function (Blueprint $table) {

            $table->bigIncrements('id');

            $table->bigInteger('salary_id');
            $table->foreign('salary_id')->references('id')->on('salary')->onDelete('cascade');

            $table->decimal('basic_salary', 16, 2)->default(0.0000);
            $table->decimal('hourly_salary', 16, 2)->nullable()->default(0.0000);
            $table->tinyInteger('nhif')->default(0);
            $table->tinyInteger('deduction_exempt')->default(0);

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
        Schema::dropIfExists('salary_histories');
    }
}
