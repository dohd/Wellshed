<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSsrDefaultInputsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ssr_default_inputs', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedInteger('client');
            $table->text('findings');
            $table->text('action');
            $table->text('recommendations');


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
        Schema::dropIfExists('ssr_default_inputs');
    }
}
