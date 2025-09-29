<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDailyBusinessMetricsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('daily_business_metrics', function (Blueprint $table) {

            $table->string('dbm_uuid')->primary();

            $table->date('date');

            $table->text('options');

            $table->unsignedInteger('ins');
            $table->foreign('ins')->references('id')->on('companies');

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
        Schema::dropIfExists('daily_business_metrics_configurations');
    }
}
