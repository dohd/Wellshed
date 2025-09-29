<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobValuationsJcsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_valuations_jcs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('job_valuation_id')->nullable();
            $table->unsignedBigInteger('equipment_id')->nullable();
            $table->integer('type')->default(1);
            $table->string('reference', '191')->nullable();
            $table->date('date')->nullable();
            $table->string('technician', '191')->nullable();
            $table->string('fault', '50')->default('none');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_valuations_jcs');
    }
}
