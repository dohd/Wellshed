<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClrCasualLabourersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clr_casual_labourers', function (Blueprint $table) {

            $table->string('clrcl_number')->primary();

            $table->string('clr_number');
            $table->foreign('clr_number')->references('clr_number')->on('casual_labourers_remunerations')->onDelete('cascade');

            $table->unsignedBigInteger('casual_labourer_id');
            $table->foreign('casual_labourer_id')->references('id')->on('casual_labourers')->onDelete('cascade');

            $table->decimal('wage', 20, 2);
            $table->decimal('hours', 20, 2);
            $table->decimal('remuneration', 20, 2);
            $table->timestamps();

            // Foreign key constraints
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('clr_casual_labourers');
    }
}
